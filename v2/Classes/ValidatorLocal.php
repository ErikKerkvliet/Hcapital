<?php

    namespace v2\Classes;

    use v2\Database\Entity\Download;
    use v2\Resolvers\HostResolver;
    use v2\Database\Entity\Host;
	use v2\RapidgatorClient;
	use v2\ClientException;
    use v2\Interfaces\ValidatorInterface;

    /**
     * Validate class to check the availability of download links.
     */
    class ValidatorLocal implements ValidatorInterface
    {
        /**
         * @var HostResolver
         */
        private $hostResolver;

        /**
         * @var array
         */
        private $rapidgatorUrls = [];

        /**
         * @var array
         */
        private $katfileUrls = [];

        /**
         * @var array
         */
        private $mexashareUrls = [];

        public function __construct()
        {
            $this->hostResolver = new HostResolver();
        }

        /**
		 * Validates if the download urls are available.
		 *
		 * @param Download[] $downloads
         * @return array
		 */
        public function validateUrlsByDownloads(array $downloads): array
        {
            foreach ($downloads as $download) {
				if (($link = $download->getLink()) && ($url = $link->getLink()) && $this->hostResolver->byUrl($url) === Host::HOST_RAPIDGATOR) {
					$this->rapidgatorUrls[$url] = $url;
				} else if ($link && $url && $this->hostResolver->byUrl($url) === Host::HOST_KATFILE) {
                    $this->katfileUrls[$url] = $url;
				} else if ($link && $url && $this->hostResolver->byUrl($url) === Host::HOST_MEXASHARE) {
                    $this->mexashareUrls[$url] = $url;
                }
			}

            $this->validateRapidgator();
            $this->validateKatfile();
            $this->validateMexashare();

            return $this->rapidgatorUrls + $this->katfileUrls + $this->mexashareUrls;
        }

        /**
		 * Validates if the links urls are available.
		 *
		 * @param Link[] $links
         * @return array
		 */
        public function validateUrlsByLinks(array $links): array
        {
            foreach ($links as $link) {
				if (($url = $link->getLink()) && $this->hostResolver->byUrl($url) === Host::HOST_RAPIDGATOR) {
					$this->rapidgatorUrls[$url] = $url;
				} else if ($url &&  $this->hostResolver->byUrl($url) === Host::HOST_KATFILE) {
                    $this->katfileUrls[$url] = $url;
				} else if ($url && $this->hostResolver->byUrl($url) === Host::HOST_MEXASHARE) {
                    $this->mexashareUrls[$url] = $url;
                }
			}

            $this->validateRapidgator();
            $this->validateKatfile();
            $this->validateMexashare();

            return $this->rapidgatorUrls + $this->katfileUrls + $this->mexashareUrls;
        }

        /**
		 * Validates if the Rapidgator links are available.
		 *
		 * @param Download[] $downloads
         * @return array
		 */
        private function validateRapidgator() : array
        {
			$client = new RapidgatorClient(getenv('RAPIDGATOR_USERNAME'), getenv('RAPIDGATOR_PASSWORD'), null);
			foreach ($this->rapidgatorUrls as $url) {
				preg_match('/\/file\/([a-f0-9]{32})\//', $url, $matches);
				$fileId = $matches[1] ?? null;
				try {
					$response = $client->getFileDetails($fileId);
				} catch (ClientException $e) {
					dd($e);
				}
				if (request('state') == '1' && $response->status !== 200 
					|| request('state') == '2' && $response->status === 200
				) {
					continue;
				}
				$this->rapidgatorUrls[$url] = $response->status === 200 ? 'available' : 'unavailable';
			}
            return $this->rapidgatorUrls;
		}

        /**
         * Validates if the katfile links are available.
         *
         * @param Download[] $downloads
         * @return array
         */
        private function validateKatfile(): array
        {
            $fileIds = [];
            $filecodeToUrl = [];

            // Extract filecodes and build mapping
            foreach ($this->katfileUrls as $url) {
                if (preg_match('#katfile\.com/([^/]+)/#', $url, $matches)) {
                    $filecode = $matches[1];
                    $fileIds[] = $filecode;
                    $filecodeToUrl[$filecode] = $url; // map filecode to full URL
                }
            }

            if (empty($fileIds)) {
                return [];
            }

            $ids = implode(',', $fileIds);
            $ch = curl_init("https://katfile.com/api/file/info?key=" . getenv('KATFILE_API_KEY') . "&file_code=" . $ids);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($response, true);

            if (!isset($data['result']) || !is_array($data['result'])) {
                return [];
            }

            foreach ($data['result'] as $item) {
                $filecode = $item['filecode'] ?? null;
                $status = $item['status'] ?? 0;

                if ($filecode && isset($filecodeToUrl[$filecode])) {
                    $url = $filecodeToUrl[$filecode];

                    if (
                        request('state') == '1' && $status !== 200 ||
                        request('state') == '2' && $status === 200
                    ) {
                        continue;
                    }

                    $this->katfileUrls[$url] = $status === 200 ? 'available' : 'unavailable';
                }
            }
            return $this->katfileUrls;
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
                $this->mexashareUrls[$url] = 'available'; // Placeholder for actual validation logic
            }   
            return $this->mexashareUrls;
        }
    }