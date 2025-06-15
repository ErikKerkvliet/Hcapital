<?php

namespace v2\Classes;

use v2\Database\Entity\Download;
use v2\Database\Entity\Link;
use v2\Resolvers\HostResolver;
use v2\Database\Entity\Host;
use v2\RapidgatorClient;
use v2\ClientException;

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

    private $hosts = [];

    public function __construct()
    {
        $this->hostResolver = new HostResolver();
    }

    /**
     * Executes the validation tasks for different hosts concurrently using pcntl_fork.
     * Modifies $this->rapidgatorUrls, $this->katfileUrls, $this->mexashareUrls with results.
     */
    private function executeValidationTasks(): void
    {
        if (!function_exists('pcntl_fork')) {
            error_log("Validator: pcntl_fork not available. Running validation sequentially.");
            if (!empty($this->rapidgatorUrls)) $this->validateRapidgator();
            if (!empty($this->katfileUrls))    $this->validateKatfile();
            if (!empty($this->mexashareUrls))  $this->validateMexashare();
            return;
        }

        $pipes = [];
        $childPids = [];

        $tasks = [];
        if (!empty($this->rapidgatorUrls)) {
            $tasks['rapidgator'] = ['method' => 'validateRapidgator', 'urls' => $this->rapidgatorUrls];
        }
        if (!empty($this->katfileUrls)) {
            $tasks['katfile'] = ['method' => 'validateKatfile', 'urls' => $this->katfileUrls];
        }
        if (!empty($this->mexashareUrls)) {
            $tasks['mexashare'] = ['method' => 'validateMexashare', 'urls' => $this->mexashareUrls];
        }

        if (empty($tasks)) {
            return; // No URLs to validate
        }

        foreach ($tasks as $host => $taskInfo) {
            $pipe_fds = @stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
            if (!$pipe_fds) {
                error_log("Validator: Failed to create stream_socket_pair for $host validation. This host's URLs will be marked as 'pipe_creation_error'.");
                // Mark these URLs as an error and skip forking for this task
                $this->{$host . 'Urls'} = array_fill_keys(array_keys($taskInfo['urls']), 'pipe_creation_error');
                unset($tasks[$host]); // Remove from tasks to process
                continue;
            }

            $pid = pcntl_fork();

            if ($pid == -1) {
                error_log("Validator: pcntl_fork failed for $host validation. This host's URLs will be marked as 'fork_error'.");
                fclose($pipe_fds[0]);
                fclose($pipe_fds[1]);
                $this->{$host . 'Urls'} = array_fill_keys(array_keys($taskInfo['urls']), 'fork_error');
                unset($tasks[$host]); // Remove from tasks to process
            } else if ($pid) {
                // Parent process
                $childPids[$pid] = $host;
                fclose($pipe_fds[1]); // Close write end in parent
                $pipes[$pid] = $pipe_fds[0]; // Keep read end
                stream_set_blocking($pipes[$pid], false); // Non-blocking read
            } else {
                // Child process
                fclose($pipe_fds[0]); // Close read end in child

                // Re-initialize dependencies if they might have state or issues with fork
                // $this->hostResolver = new HostResolver(); // Example if HostResolver had state

                // Set the specific URLs for this child's task on its copy of $this
                $this->rapidgatorUrls = ($host === 'rapidgator') ? $taskInfo['urls'] : [];
                $this->katfileUrls    = ($host === 'katfile')    ? $taskInfo['urls'] : [];
                $this->mexashareUrls  = ($host === 'mexashare')  ? $taskInfo['urls'] : [];
                
                // Call the original validation method (e.g., $this->validateRapidgator())
                // This modifies the corresponding $this->[host]Urls property in the child's memory.
                $this->{$taskInfo['method']}();

                // Get the result from the modified property
                $result = $this->{$host . 'Urls'}; // e.g., $this->rapidgatorUrls

                $serializedResult = @serialize($result);
                if ($serializedResult === false) {
                    // This should ideally not happen with simple arrays of strings
                    // Log error and exit child gracefully if possible
                    error_log("Child process for $host: Failed to serialize results.");
                    // Consider writing an error marker or empty string to pipe if serialization fails.
                    // For now, an empty write will be handled by parent as "no_data_error".
                    fwrite($pipe_fds[1], serialize(['error' => 'child_serialization_failed']));
                } else {
                    fwrite($pipe_fds[1], $serializedResult);
                }
                
                fclose($pipe_fds[1]);
                exit(0); // Child exits successfully
            }
        }

        // Parent process: collect results
        $resultsFromChildren = [];
        foreach (array_keys($tasks) as $host) { // Only for tasks that were attempted to be forked
            $resultsFromChildren[$host] = null; 
        }

        $activePids = $childPids;
        while (count($activePids) > 0) {
            foreach ($activePids as $pid => $hostKey) {
                $status = null;
                $res = pcntl_waitpid($pid, $status, WNOHANG);

                if ($res == -1 || $res > 0) { // Child exited or error
                    unset($activePids[$pid]); // Remove from active list
                    if (isset($pipes[$pid]) && is_resource($pipes[$pid])) {
                        $pipeContent = '';
                        while (!feof($pipes[$pid])) { // Read until EOF
                            $readChunk = fread($pipes[$pid], 8192);
                            if ($readChunk === false || $readChunk === '') break;
                            $pipeContent .= $readChunk;
                        }
                        fclose($pipes[$pid]);

                        if (!empty($pipeContent)) {
                            $data = @unserialize($pipeContent);
                            if ($data !== false) {
                                $resultsFromChildren[$hostKey] = $data;
                            } else {
                                error_log("Validator: Failed to unserialize data from child PID $pid for host $hostKey.");
                                $resultsFromChildren[$hostKey] = 'unserialize_error';
                            }
                        } else {
                             error_log("Validator: No data received from child PID $pid for host $hostKey.");
                             $resultsFromChildren[$hostKey] = 'no_data_error';
                        }
                    } else {
                        error_log("Validator: Pipe for PID $pid (host $hostKey) was not available for reading.");
                        $resultsFromChildren[$hostKey] = 'pipe_read_error';
                    }
                }
            }
            if (empty($activePids)) break;
            usleep(50000); // 50ms sleep to prevent busy-looping
        }
        
        // Final cleanup for any PIDs that were missed (should be rare with WNOHANG loop)
        foreach ($activePids as $pid => $hostKey) {
            pcntl_waitpid($pid, $status); // Blocking wait
            if (isset($pipes[$pid]) && is_resource($pipes[$pid])) fclose($pipes[$pid]);
            if ($resultsFromChildren[$hostKey] === null) {
                error_log("Validator: Child PID $pid (host $hostKey) results not collected, marking as error.");
                $resultsFromChildren[$hostKey] = 'collection_error';
            }
        }

        // Update the main object's properties with results
        foreach ($tasks as $host => $taskInfo) { // Iterate over tasks that were supposed to run
            $originalUrlsForHost = $taskInfo['urls'];
            if (isset($resultsFromChildren[$host]) && is_array($resultsFromChildren[$host])) {
                $this->{$host . 'Urls'} = $resultsFromChildren[$host];
            } else {
                // An error occurred, or fork/pipe failed for this host.
                // If it was already set due to pre-fork error (e.g. pipe_creation_error), don't overwrite.
                if (empty($this->{$host . 'Urls'}) || $this->{$host . 'Urls'} === $originalUrlsForHost) {
                     $errorStatus = $resultsFromChildren[$host] ?: 'processing_error'; // general error if null
                     $this->{$host . 'Urls'} = array_fill_keys(array_keys($originalUrlsForHost), (string)$errorStatus);
                }
            }
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

        foreach ($downloads as $download) {
            $linkEntity = $download->getLink();
            if ($linkEntity && ($url = $linkEntity->getLink()) && is_string($url)) {
                $this->filterUrls($url);
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

        foreach ($links as $linkEntity) {
            if (($url = $linkEntity->getLink()) && is_string($url)) {
                $this->filterUrls($url);
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
     * Modifies $this->katfileUrls
     */
    private function validateKatfile(): void
    {
        if (empty($this->katfileUrls)) return;

        $fileIds = [];
        $filecodeToUrl = [];
        $originalUrls = $this->katfileUrls;
        $processedUrls = []; // Initialize with original values, will be overwritten

        foreach ($originalUrls as $url => $originalValue) {
            $processedUrls[$url] = $url; // Default to url=>url if not processed or filtered
            if (preg_match('#katfile\.com/([^/]+)/#', $url, $matches)) {
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
        
        $curlUrl = "https://katfile.com/api/file/info?key=" . $apiKey . "&file_code=" . $ids;
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

                $isAvailable = ($status == 200); // Assuming 200 means available
                $stateFilter = request('state');

                if (($stateFilter === '1' && !$isAvailable) || ($stateFilter === '2' && $isAvailable)) {
                    // $processedUrls[$url] remains $url (already set as default)
                    continue;
                }
                $processedUrls[$url] = $isAvailable ? 'available' : 'unavailable';
            }
        }
        
        // For filecodes sent but not in API response (Katfile might ignore invalid ones)
        foreach ($filecodeToUrl as $filecode => $url) {
            if (!isset($respondedFilecodes[$filecode])) {
                // Not in response -> treat as unavailable unless filtered out by 'want available'
                $stateFilter = request('state');
                if ($stateFilter === '1') { // Want available, this one is not. Keep $url=>$url
                    // $processedUrls[$url] remains $url
                } else { // No filter, or want unavailable (state 2). This one is unavailable.
                    $processedUrls[$url] = 'unavailable';
                }
            }
        }
        $this->katfileUrls = $processedUrls;
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