<?php

namespace v2\Classes;

use v2\Database\Entity\Download;
use v2\Database\Entity\Link;
use v2\Resolvers\HostResolver;
use v2\Database\Entity\Host;
use v2\RapidgatorClient;
use v2\ClientException;
use v2\Database\Entity\InvalidLink;
use v2\Factories\InvalidLinkFactory;

class ValidatorLocal
{
    /**
     * @var HostResolver
     */
    private $hostResolver;

    /**
     * @var array<string, string>
     */
    private $rapidgatorUrls = [];

    /**
     * @var array<string, string>
     */
    private $katfileUrls = [];

    /**
     * @var array<string, string>
     */
    private $mexashareUrls = [];

    /**
     * @var array<string, array{link_id: int, entry_id: int}>
     */
    private $urlToLinkData = [];

    private $hosts = [];

    public function __construct()
    {
        $this->hostResolver = new HostResolver();
    }

    private function executeValidationTasks(): void
    {
        // Running validation sequentially to prevent race conditions and duplicate key errors.
        if (!empty($this->rapidgatorUrls)) {
            $this->validateRapidgator();
        }
        if (!empty($this->katfileUrls)) {
            $this->validateKatfile();
        }
        if (!empty($this->mexashareUrls)) {
            $this->validateMexashare();
        }
    }

    /**
	 * Validates if the download urls are available.
	 *
	 * @param Download[] $downloads
     * @return array<string, string>
	 */
    public function validateUrlsByDownloads(array $downloads, array $hosts = [Host::HOST_RAPIDGATOR]): array
    {
        $this->hosts = $hosts;
        $this->urlToLinkData = [];

        foreach ($downloads as $download) {
            $linkEntity = $download->getLink();
            if ($linkEntity && ($url = $linkEntity->getUrl()) && is_string($url)) {
                $this->filterUrls($url);
                $this->urlToLinkData[$url] = [
                    'link_id' => $linkEntity->getId(),
                    'entry_id' => $linkEntity->getEntry(true),
                ];
            }
		}

        $this->executeValidationTasks();

        return $this->getResponse();
    }

    /**
	 * Validates if the links urls are available.
	 *
	 * @param Link[] $links
     * @return array<string, string>
	 */
    public function validateUrlsByLinks(array $links, array $hosts = [Host::HOST_RAPIDGATOR]): array
    {
        $this->hosts = $hosts;
        $this->urlToLinkData = [];

        foreach ($links as $linkEntity) {
            if (($url = $linkEntity->getUrl()) && is_string($url)) {
                $this->filterUrls($url);
                $this->urlToLinkData[$url] = [
                    'link_id' => $linkEntity->getId(),
                    'entry_id' => $linkEntity->getEntry(true),
                ];
            }
		}

        $this->executeValidationTasks();

        return $this->getResponse();
    }

    /**
	 * Validates if the Rapidgator links are available.
	 * Modifies $this->rapidgatorUrls
	 */
    private function validateRapidgator(): void
    {
        $urlChunks = array_chunk($this->rapidgatorUrls, 24, true); // Keep keys

        try {
            $client = new RapidgatorClient(getenv('RAPIDGATOR_USERNAME'), getenv('RAPIDGATOR_PASSWORD'), null);
            $invalidLinksToPersist = [];
            $validLinksToRemove = []; // [NEW] List for links that are now valid

            $invalidLinkRepository = app('em')->getRepository(InvalidLink::class);
            $invalidLinkFactory = new InvalidLinkFactory();

            foreach ($urlChunks as $currentChunk) {
                // Call the checkLink method with the current, smaller chunk of URLs.
                $response = $client->checkLink(array_keys($currentChunk));
                
                foreach ($response as $item) {
                    $url = $item->url;
                    $status = $item->status;
                    $resultStatus = $status === 'ACCESS' ? 'available' : 'unavailable';
                    $this->rapidgatorUrls[$url] = $resultStatus;
                    
                    if (isset($this->urlToLinkData[$url])) {
                        $linkData = $this->urlToLinkData[$url];
                        $existingInvalidLink = $invalidLinkRepository->findOneBy(['link' => $linkData['link_id']]);

                        if ($resultStatus === 'available' && $existingInvalidLink) {
                            // [NEW] Link is valid and exists in invalid_links, so we should remove it.
                            $validLinksToRemove[] = $existingInvalidLink;
                        } elseif ($resultStatus === 'unavailable' && !$existingInvalidLink) {
                            // [MODIFIED] Link is invalid and does NOT exist in invalid_links, so add it.
                            $invalidLink = $invalidLinkFactory->create([
                                'entry' => $linkData['entry_id'],
                                'link' => $linkData['link_id'],
                            ]);
                            $invalidLinksToPersist[] = $invalidLink;
                        }
                    }
                }
            }

            // [NEW] Batch process deletions
            if (!empty($validLinksToRemove)) {
                foreach ($validLinksToRemove as $linkEntity) {
                    app('em')->delete($linkEntity);
                }
            }

            // [NEW] Batch process insertions
            if (!empty($invalidLinksToPersist)) {
                foreach ($invalidLinksToPersist as $linkEntity) {
                    app('em')->persist($linkEntity);
                }
            }
            
            // [NEW] Flush all changes at once
            if (!empty($validLinksToRemove) || !empty($invalidLinksToPersist)) {
                app('em')->flush();
            }

        } catch (ClientException $e) {
            throw new \Exception("Rapidgator API Error: " . $e->getMessage());
        }
    }

    /**
     * Validates if the katfile links are available.
     * Modifies $this->katfileUrls
     */
    private function validateKatfile(): void
    {
        if (empty($this->katfileUrls)) return;

        // [NEW] Initialize lists for DB operations and repositories
        $invalidLinksToPersist = [];
        $validLinksToRemove = [];
        $invalidLinkRepository = app('em')->getRepository(InvalidLink::class);
        $invalidLinkFactory = new InvalidLinkFactory();

        $fileIds = [];
        $filecodeToUrl = [];
        $originalUrls = $this->katfileUrls;
        $processedUrls = []; // Initialize with original values, will be overwritten

        foreach ($originalUrls as $url => $originalValue) {
            $processedUrls[$url] = $url; // Default to url=>url if not processed or filtered
            if (preg_match('#katfile\.com/([^/]+)/#', $url, $matches) || preg_match('#katfile\.cloud/([^/]+)/#', $url, $matches)) {
                $filecode = $matches[1];
                $fileIds[] = $filecode;
                $filecodeToUrl[$filecode] = $url;
            } else {
                $processedUrls[$url] = 'error_invalid_url_format';
            }
        }

        if (empty($fileIds)) { // All URLs were invalid format, or initial list was empty
            $this->katfileUrls = $processedUrls;
            return;
        }

        $ids = implode(',', $fileIds);
        $apiKey = getenv('KATFILE_API_KEY');
        if (!$apiKey) {
            error_log("Validator: KATFILE_API_KEY is not set.");
            foreach ($filecodeToUrl as $filecode => $url) { // Mark only those that were valid format
                 $processedUrls[$url] = 'error_api_key_missing';
            }
            $this->katfileUrls = $processedUrls;
            return;
        }
        
        $curlUrl = "https://katfile.cloud/api/file/info?key=" . $apiKey . "&file_code=" . $ids;
        $ch = curl_init($curlUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || !empty($curlError)) {
            error_log("Validator: Katfile API cURL error for " . $curlUrl . " : " . $curlError);
            foreach ($filecodeToUrl as $filecode => $url) {
                $processedUrls[$url] = 'error_api_request_failed';
            }
            $this->katfileUrls = $processedUrls;
            return;
        }

        $data = json_decode($response, true);

        if (!isset($data['result']) || !is_array($data['result'])) {
            error_log("Validator: Katfile API bad response format for " . $curlUrl . " : " . substr($response, 0, 200));
            foreach ($filecodeToUrl as $filecode => $url) {
                $processedUrls[$url] = 'error_api_bad_response';
            }
            $this->katfileUrls = $processedUrls;
            return;
        }

        $respondedFilecodes = [];
        foreach ($data['result'] as $item) {
            $filecode = $item['filecode'] ?? null;
            $status = $item['status'] ?? null; 

            if ($filecode && isset($filecodeToUrl[$filecode])) {
                $url = $filecodeToUrl[$filecode];
                $respondedFilecodes[$filecode] = true;

                if ($status === null) {
                    $processedUrls[$url] = 'error_api_missing_status';
                    continue;
                }

                $isAvailable = ($status == 200);
                $processedUrls[$url] = $isAvailable ? 'available' : 'unavailable';
                
                // [NEW] Synchronization logic
                if (isset($this->urlToLinkData[$url])) {
                    $linkData = $this->urlToLinkData[$url];
                    $existingInvalidLink = $invalidLinkRepository->findOneBy(['link' => $linkData['link_id']]);

                    if ($isAvailable && $existingInvalidLink) {
                        $validLinksToRemove[] = $existingInvalidLink;
                    } elseif (!$isAvailable && !$existingInvalidLink) {
                        $invalidLink = $invalidLinkFactory->create([
                            'entry' => $linkData['entry_id'],
                            'link' => $linkData['link_id'],
                        ]);
                        $invalidLinksToPersist[] = $invalidLink;
                    }
                }
            }
        }
        
        // [NEW] Handle filecodes that were not in the API response (implying they are unavailable)
        foreach ($filecodeToUrl as $filecode => $url) {
            if (!isset($respondedFilecodes[$filecode])) {
                $processedUrls[$url] = 'unavailable';
                if (isset($this->urlToLinkData[$url])) {
                    $linkData = $this->urlToLinkData[$url];
                    $existingInvalidLink = $invalidLinkRepository->findOneBy(['link' => $linkData['link_id']]);
                    if (!$existingInvalidLink) {
                        $invalidLink = $invalidLinkFactory->create([
                            'entry' => $linkData['entry_id'],
                            'link' => $linkData['link_id'],
                        ]);
                        $invalidLinksToPersist[] = $invalidLink;
                    }
                }
            }
        }
        
        $this->katfileUrls = $processedUrls;

        // [NEW] Batch process all DB changes at the end
        if (!empty($validLinksToRemove)) {
            foreach ($validLinksToRemove as $linkEntity) {
                app('em')->delete($linkEntity);
            }
        }

        if (!empty($invalidLinksToPersist)) {
            foreach ($invalidLinksToPersist as $linkEntity) {
                app('em')->persist($linkEntity);
            }
        }
        
        if (!empty($validLinksToRemove) || !empty($invalidLinksToPersist)) {
            app('em')->flush();
        }
    }

    /**
     * Validates if the mexashare links are available.
     *
     * @param Download[] $downloads
     * @return array
     */
    private function validateMexashare(): array
    {
        foreach ($this->mexashareUrls as $url) {
            $this->mexashareUrls[$url] = 'available';
        }   
        return $this->mexashareUrls;
    }

    private function filterUrls(string $url): void
    {
        $hostType = $this->hostResolver->byUrl($url);
        if ($hostType === Host::HOST_RAPIDGATOR) {
            $this->rapidgatorUrls[$url] = $url;
        } else if ($hostType === Host::HOST_KATFILE) {
            $this->katfileUrls[$url] = $url;
        } else if ($hostType === Host::HOST_MEXASHARE) {
            $this->mexashareUrls[$url] = $url;
        }
    }

    private function getResponse(): array
    {
        $urls = [];
        if (in_array(Host::HOST_RAPIDGATOR, $this->hosts)) {
            $urls += $this->rapidgatorUrls;
        }
        if (in_array(Host::HOST_MEXASHARE, $this->hosts)) {
            $urls += $this->mexashareUrls;
        }
        if (in_array(Host::HOST_KATFILE, $this->hosts)) {
            $urls += $this->katfileUrls;
        }
        return $urls;
    }
}