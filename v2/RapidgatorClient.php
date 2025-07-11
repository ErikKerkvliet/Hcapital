<?php

	namespace v2;

	class ClientException extends \Exception
	{
	}

	/**
	 * Rapidgator API client Class
	 */
	class RapidgatorClient
	{
		const BASE_URL = 'https://rapidgator.net/api/v2/';
		const AUTH_TIMEOUT = 400;

		const MODE_ID_PUBLIC = 0;
		const MODE_ID_PREMIUM_ONLY = 1;
		const MODE_ID_PRIVATE = 2;
		const MODE_ID_HOT = 3;

		const UPLOAD_STATE_UPLOADING = 0;
		const UPLOAD_STATE_PROCESSING = 1;
		const UPLOAD_STATE_DONE = 2;
		const UPLOAD_STATE_FAIL = 3;

		protected $_ch = null;
		protected $_token;
		protected $_login;
		protected $_password;
		protected $_code;
		protected $_last_access_time = 0;

		/**
		 * @param string|null $login
		 * @param string|null $password
		 * @param callable|null $code
		 * @example
		 *     $client = new Client('your_login','your_password', function () {
		 *         return get_auth_code('your_secret');
		 *     });
		 */
		public function __construct(?string $login, ?string $password, ?callable $code)
		{
			if (!empty($login)) {
				$this->login($login, $password, $code);
			}
		}

		/**
		 * Generates a Access Token to be used in upcoming API requests
		 *
		 * @param string $login
		 * @param string $password
		 * @param callable|null $code - an anonymous client function that returns a confirmation code based on a secret code
		 * @return mixed
		 */
		public function login(string $login, string $password, ?callable $code)
		{
			$response = $this->_process('user/login', [
				'login' => $login, 'password' => $password, 'code' => $code ? $code() : null
			]);

			$this->_token = $response->token;
			$this->_login = $login;
			$this->_password = $password;
			$this->_code = $code;
			$this->_last_access_time = time();

			return $response;
		}

		protected function _process($method, array $data = [])
		{
			if ($method != 'user/login' && !empty($this->_token)) {
				if (time() - $this->_last_access_time > self::AUTH_TIMEOUT) {
					$this->_token = null;
					$this->login($this->_login, $this->_password, $this->_code);
				}
			}

			if ($this->_ch === null) {
				$this->_ch = curl_init();
				curl_setopt($this->_ch, CURLOPT_HEADER, false);
				curl_setopt($this->_ch, CURLOPT_TIMEOUT, 600); // set timeout to 10 minutes
				curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, true);
			}

			$data = $this->_getRequestData($data);
			$url = self::BASE_URL . $method . ((!empty($data)) ? '?' . http_build_query($data) : '');

			curl_setopt($this->_ch, CURLOPT_URL, $url);
			$result = curl_exec($this->_ch);
			$http_code = curl_getinfo($this->_ch, CURLINFO_HTTP_CODE);
			if ($method == 'file/info') {
				return json_decode($result);
			}
			return $this->_processResponse($http_code, $result);
		}

		protected function _getRequestData(array $data)
		{
			if (!empty($this->_token)) {
				$data = array_merge(['token' => $this->_token], $data);
			}

			return $data;
		}

		protected function _processResponse($http_code, $result)
		{
			if (!preg_match('/2\d+/', $http_code)) {
				throw new ClientException('Server error, got HTTP code: ' . $http_code);
			}

			$result = json_decode($result);

			if ($result->status != 200) {
				throw new ClientException('Error, Code: ' . $result->status . ' Message: ' . $result->details);
			}

			return $result->response;
		}

		/**
		 * Returns a list of the user's personal information
		 *
		 * @return mixed
		 */
		public function getUserInfo()
		{
			return $this->_process('user/info')->user;
		}

		/**
		 * Rename a file
		 *
		 * @param $name
		 * @param $file_id
		 *
		 * @return mixed
		 */
		public function renameFile($name, $file_id)
		{
			return $this->_process('file/rename', ['name' => $name, 'file_id' => $file_id])->file;
		}

		/**
		 * Returns a file's details
		 *
		 * @param $file_id
		 *
		 * @return mixed
		 */
		public function getFileInfo($file_id)
		{
			return $this->_process('file/info', ['file_id' => $file_id])->file;
		}

		/**
		 * Returns a file's details
		 *
		 * @param $file_id
		 *
		 * @return mixed
		 */
		public function getFileDetails($file_id)
		{
			return $this->_process('file/info', ['file_id' => $file_id]);
		}

		/**
		 * Upload file
		 *
		 * @param string $file_path
		 * @param null $folder_id
		 * @param null $name
		 * @param false $multipart
		 *
		 * @return mixed
		 * @throws ClientException
		 */
		public function uploadFile($file_path, $folder_id = null, $name = null, $multipart = false/*, $mode = null*/)
		{
			if (!file_exists($file_path)) {
				throw new ClientException('File doesn\'t exists: ' . $file_path);
			}

			$md5 = md5_file($file_path);
			$size = filesize($file_path);

			if (!$name) {
				$name = basename($file_path);
			}

			$response = $this->_process('file/upload', ['name' => $name, 'hash' => $md5, 'size' => $size, 'folder_id' => $folder_id, 'multipart' => $multipart/*, 'mode' => $mode*/]);

			$upload = $response->upload;
			$file = (array)$upload->file;

			if (empty($file)) {
				$this->_processUpload($upload->url, $file_path, $multipart);

				$upload_id = $upload->upload_id;

				while (true) {
					sleep(1);
					$upload = $this->getFileUploadInfo($upload_id);

					if ($upload->state != self::UPLOAD_STATE_PROCESSING) {
						break;
					}
				}

				if (!empty($upload->error)) {
					throw new ClientException('Error, Code: ' . $upload->error->code . ' Message: ' . $upload->error->message);
				}
			}

			return $upload->file;
		}

		protected function _processUpload($upload_url, $file_path, $multipart)
		{
			$file_path = realpath($file_path);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $upload_url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			if ($multipart) {
				if (version_compare(PHP_VERSION, '5.5.0') >= 0) {
					$arg = new \CURLFile($file_path);
				} else {
					$arg = '@' . $file_path;
				}

				curl_setopt($ch, CURLOPT_POSTFIELDS, ['file' => $arg]);
			} else {
				curl_setopt($ch, CURLOPT_INFILE, ($handle = fopen($file_path, 'r')));
				curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file_path));
				curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/octet-stream", "Transfer-Encoding: chunked"]);
			}

			$result = curl_exec($ch);

			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

			curl_close($ch);

			if ($handle) {
				fclose($handle);
			}

			return $this->_processResponse($http_code, $result);
		}

		/**
		 * Checks upload session state
		 *
		 * @param $upload_id
		 *
		 * @return mixed
		 */
		public function getFileUploadInfo($upload_id)
		{
			return $this->_process('file/upload_info', ['upload_id' => $upload_id])->upload;
		}

		/**
		 * Download a file
		 *
		 * @param $file_id
		 *
		 * @return mixed
		 */
		public function downloadFile($file_id)
		{
			return $this->_process('file/download', ['file_id' => $file_id])->download_url;
		}

		/**
		 * Returns a trash can file's details
		 *
		 * @param null $page - Page number. Default is 1
		 * @param null $per_page - Number of files per page. Default is 500
		 * @param null $sort_column - Sort column name. Possible values: 'name', 'created', 'size', 'nb_downloads'. Default is 'name'
		 * @param null $sort_direction - Sort direction. Possible values: 'ASC', 'DESC'. Default is 'ASC'
		 *
		 * @return mixed
		 */
		public function getTrashCanContent($page = null, $per_page = null, $sort_column = null, $sort_direction = null)
		{
			return $this->_process(
				'trashcan/content',
				[
					'page' => $page,
					'per_page' => $per_page,
					'sort_column' => $sort_column,
					'sort_direction' => $sort_direction,
				]
			)->files;
		}

		/**
		 * Empty trash can
		 *
		 * @param null $file_ids
		 *
		 * @return mixed
		 */
		public function emptyTrashCan($file_ids = null)
		{
			if (is_array($file_ids)) {
				$file_ids = implode(',', $file_ids);
			}

			return $this->_process('trashcan/empty', ['file_id' => $file_ids])->result;
		}

		/**
		 * Restore file(s) to root folder
		 *
		 * @param null $file_ids
		 *
		 * @return mixed
		 */
		public function restoreTrashCan($file_ids = null)
		{
			if (is_array($file_ids)) {
				$file_ids = implode(',', $file_ids);
			}

			return $this->_process('trashcan/restore', ['file_id' => $file_ids])->result;
		}

		/**
		 * Copy a file to another folder
		 *
		 * @param $folder_id_dest
		 * @param $file_ids
		 *
		 * @return mixed
		 */
		public function copyFile($folder_id_dest, $file_ids)
		{
			if (is_array($file_ids)) {
				$file_ids = implode(',', $file_ids);
			}

			return $this->_process('file/copy', ['folder_id_dest' => $folder_id_dest, 'file_id' => $file_ids])->result;
		}

		/**
		 * Move a file to another folder
		 *
		 * @param $folder_id_dest
		 * @param $file_ids
		 *
		 * @return mixed
		 */
		public function moveFile($folder_id_dest, $file_ids)
		{
			if (is_array($file_ids)) {
				$file_ids = implode(',', $file_ids);
			}

			return $this->_process('file/move', ['folder_id_dest' => $folder_id_dest, 'file_id' => $file_ids])->result;
		}

		/**
		 * Delete a file. The file is basically moved to the Trash Bin
		 *
		 * @param $file_ids
		 *
		 * @return mixed
		 */
		public function deleteFile($file_ids)
		{
			if (is_array($file_ids)) {
				$file_ids = implode(',', $file_ids);
			}

			return $this->_process('file/delete', ['file_id' => $file_ids])->result;
		}

		/**
		 * Change a file mode
		 *
		 * @param $mode
		 * @param $file_id
		 *
		 * @return mixed
		 */
		public function changeFileMode($mode, $file_id)
		{
			return $this->_process('file/change_mode', ['file_id' => $file_id, 'mode' => $mode])->file;
		}

		/**
		 * Check a file link
		 *
		 * @param $url
		 *
		 * @return mixed
		 */
		public function checkLink($url)
		{
			if (is_array($url)) {
				$url = implode(',', $url);
			}

			return $this->_process('file/check_link', ['url' => $url]);
		}

		/**
		 * Create one time download link
		 *
		 * @param $file_id
		 * @param $notify
		 * @param $callback_url
		 *
		 * @return mixed
		 */
		public function createOneTimeDownload($file_id, $notify = false, $callback_url = false)
		{
			return $this->_process('file/onetimelink_create', ['file_id' => $file_id, 'notify' => $notify, 'url' => $callback_url])->link;
		}

		/**
		 * Returns a one time downloads details
		 *
		 * @param null $download_ids
		 *
		 * @return mixed
		 */
		public function getOneTimeDownloadInfo($link_ids = null)
		{
			if (is_array($link_ids)) {
				$link_ids = implode(',', $link_ids);
			}

			return $this->_process('file/onetimelink_info', ['link_id' => $link_ids])->links;
		}

		/**
		 * Create a remote upload job(s)
		 *
		 * @param $urls
		 *
		 * @return mixed
		 */
		public function createRemoteUploadJob($urls)
		{
			if (is_array($urls)) {
				$urls = implode(',', $urls);
			}

			return $this->_process('remote/create', ['url' => $urls])->jobs;
		}

		/**
		 * Delete a remote upload job
		 *
		 * @param $job_ids
		 *
		 * @return mixed
		 */
		public function deleteRemoteUploadJob($job_ids)
		{
			if (is_array($job_ids)) {
				$job_ids = implode(',', $job_ids);
			}

			return $this->_process('remote/delete', ['job_id' => $job_ids])->result;
		}

		/**
		 * Returns a remote upload job's details
		 *
		 * @param null $job_ids
		 *
		 * @return mixed
		 */
		public function getRemoteUploadJobInfo($job_ids = null)
		{
			if (is_array($job_ids)) {
				$job_ids = implode(',', $job_ids);
			}

			return $this->_process('remote/info', ['job_id' => $job_ids])->jobs;
		}

		/**
		 * Returns a folder's, list of sub folders and list of files details
		 *
		 * @param null $folder_id
		 * @param null $page - Page number. Default is 1
		 * @param null $per_page - Number of files per page. Default is 500
		 * @param null $sort_column - Sort column name. Possible values: 'name', 'created', 'size', 'nb_downloads'. Default is 'name'
		 * @param null $sort_direction - Sort direction. Possible values: 'ASC', 'DESC'. Default is 'ASC'
		 *
		 * @return mixed
		 */
		public function getFolderContent($folder_id = null, $page = null, $per_page = null, $sort_column = null, $sort_direction = null)
		{
			return $this->_process(
				'folder/content',
				[
					'folder_id' => $folder_id,
					'page' => $page,
					'per_page' => $per_page,
					'sort_column' => $sort_column,
					'sort_direction' => $sort_direction,
				]
			)->folder;
		}

		/**
		 * Create a folder
		 *
		 * @param      $name
		 * @param null $folder_id
		 * @param null $mode
		 *
		 * @return mixed
		 */
		public function createFolder($name, $folder_id = null, $mode = null)
		{
			return $this->_process('folder/create', ['name' => $name, 'folder_id' => $folder_id, 'mode' => $mode])->folder;
		}

		/**
		 * Delete a folder
		 *
		 * @param $folder_ids
		 *
		 * @return mixed
		 */
		public function deleteFolder($folder_ids)
		{
			if (is_array($folder_ids)) {
				$folder_ids = implode(',', $folder_ids);
			}

			return $this->_process('folder/delete', ['folder_id' => $folder_ids])->result;
		}

		/**
		 * Copy a folder to another folder
		 *
		 * @param $folder_id_dest
		 * @param $folder_ids
		 *
		 * @return mixed
		 */
		public function copyFolder($folder_id_dest, $folder_ids)
		{
			if (is_array($folder_ids)) {
				$folder_ids = implode(',', $folder_ids);
			}

			return $this->_process('folder/copy', ['folder_id_dest' => $folder_id_dest, 'folder_id' => $folder_ids])->result;
		}

		/**
		 * Move a folder to another folder
		 *
		 * @param $folder_id_dest
		 * @param $folder_ids
		 *
		 * @return mixed
		 */
		public function moveFolder($folder_id_dest, $folder_ids)
		{
			if (is_array($folder_ids)) {
				$folder_ids = implode(',', $folder_ids);
			}

			return $this->_process('folder/move', ['folder_id_dest' => $folder_id_dest, 'folder_id' => $folder_ids])->result;
		}

		/**
		 * Rename a folder
		 *
		 * @param $name
		 * @param $folder_id
		 *
		 * @return mixed
		 */
		public function renameFolder($name, $folder_id)
		{
			return $this->_process('folder/rename', ['name' => $name, 'folder_id' => $folder_id])->folder;
		}

		/**
		 * Change a folder mode
		 *
		 * @param $mode
		 * @param $folder_id
		 *
		 * @return mixed
		 */
		public function changeFolderMode($mode, $folder_id)
		{
			return $this->_process('folder/change_mode', ['folder_id' => $folder_id, 'mode' => $mode])->folder;
		}

		/**
		 * Returns a folder's and list of sub folders details
		 *
		 * @param null $folder_id
		 *
		 * @return mixed
		 */
		public function getFolderInfo($folder_id = null)
		{
			return $this->_process('folder/info', ['folder_id' => $folder_id])->folder;
		}

	}
