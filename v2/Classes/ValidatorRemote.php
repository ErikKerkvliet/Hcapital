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
    class ValidatorRemote implements ValidatorInterface
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

        /**
         * @var array
         */
        private $hosts = [];

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
        public function validateUrlsByDownloads(array $downloads, array $hosts = [HOST::HOST_RAPIDGATOR]): array
        {
            $this->hosts = $hosts;

            foreach ($downloads as $download) {
                $linkEntity = $download->getLink();
                if ($linkEntity && ($url = $linkEntity->getLink()) && is_string($url)) {
                    $this->filterUrls($url);
                }
            }

            $this->validate();

            return $this->getResponse();
        }

        /**
		 * Validates if the links urls are available.
		 *
		 * @param Link[] $links
         * @return array
		 */
        public function validateUrlsByLinks(array $links, array $hosts = [HOST::HOST_RAPIDGATOR]): array
        {
            $this->hosts = $hosts;

            foreach ($links as $linkEntity) {
                if (($url = $linkEntity->getLink()) && is_string($url)) {
                    $this->filterUrls($url);
                }
            }

            $this->validate();

            return $this->getResponse();
        }

        /**
         * Validates if the Rapidgator links are available.
         * Modifies $this->rapidgatorUrls
         */
        private function validateRapidgator(): void
        {
            $urlChunks = array_chunk($this->rapidgatorUrls, 24);

            try {
                $client = new RapidgatorClient(getenv('RAPIDGATOR_USERNAME'), getenv('RAPIDGATOR_PASSWORD'), null);
                foreach ($urlChunks as $currentChunk) {
                    // Call the checkLink method with the current, smaller chunk of URLs.
                    $response = $client->checkLink($currentChunk);
                    
                    foreach ($response as $item) {
                        // Note: We use object property access (->) because the client decodes JSON into objects.
                        $url = $item->url;
                        $status = $item->status;
                        
                        $this->rapidgatorUrls[$url] = $status === 'ACCESS' ? 'available' : 'unavailable';
                    }
                }
            } catch (ClientException $e) {
                throw new \Exception("Rapidgator API Error: " . $e->getMessage());
            }
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
                $this->mexashareUrls[$url] = 'available';
            }   
            return $this->mexashareUrls;
        }

        private function validate(): void
        {
            if (in_array(Host::HOST_RAPIDGATOR, $this->hosts)) {
                $this->validateRapidgator();
            }
            if (in_array(Host::HOST_MEXASHARE, $this->hosts)) {
                $this->validateMexashare();
            }
            if (in_array(Host::HOST_KATFILE, $this->hosts)) {
                $this->validateKatfile();
            }
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