<?php

	use v2\Database\EntityManager;
	use v2\Database\Entity\Log;

	function setupGlobals()
	{
		session_start();

		$_SESSION['d'][] = [
			'p' => base64_encode($_SERVER['QUERY_STRING']),
		];

		$GLOBALS['EntityManager'] = new v2\Database\EntityManager();

		$loginTime = isset($_SESSION['login_time']) ? $_SESSION['login_time'] : 0;
		if(time() - $loginTime >= 1800){
			session_destroy();
			$_SESSION['banned'] = \v2\Classes\AdminCheck::isBanned();
		} else {
			$_SESSION['login_time'] = time();
		}

		$GLOBALS['source'] = 'index';

		$GLOBALS['dd'] = '';
	}

	/**
	 * @param $type
	 * @return EntityManager|mysqli|bool|null
	 */
	function app($type)
	{
		if ($type === 'em') {
			return $GLOBALS['EntityManager'] ?: null;
		}
		if ($type === 'connection') {
			if ($GLOBALS['EntityManager']) {
				return $GLOBALS['EntityManager']->getConnection();
			}
			return false;
		}
		if ($type === 'admin') {
			return $GLOBALS['Admin'];
		}
		return null;
	}

	function getImages($entity, $type, $extra = 'm')
	{
//		if (! \v2\Classes\AdminCheck::check18() || ($type == 'entry' && \v2\Classes\AdminCheck::isBanned($entity))) {
//			if ($type == 'entry') {
//				return [];
//			} else {
//				return '';
//			}
//		}

		$id = is_int($entity) ? $entity : $entity->getId();

		if ($type == 'cover') {
			return '/entry_images/entries/' . $id .'/cover/_cover_' . $extra . '.jpg';
		}
		if ($type == 'entry') {
			$dir = './entry_images/entries/' . $id .'/cg/';

			if (is_dir($dir)) {
				return array_values(array_filter(scandir($dir), function($file) use ($dir, $id) {
					return ! is_dir($dir . $file);
				}));
			}
			return [];
		}

		if($type != 'char') {
			return [];
		}

		$dir = './entry_images/char/' . $id .'/';
		if ($extra == 'tumbnail') {
			return $dir . '__img.jpg';
		}

		if (is_dir($dir)) {
			$fileNames = array_filter(scandir($dir), function($file) use ($dir) {
				return (! is_dir($file) && $file !== '__img.jpg');
			});

			return array_map(function ($file) use ($dir) {
				return $dir . $file;
			}, $fileNames);
		}
		return [];
	}

	function getImagePath($file, $id, $size, $folder, $char_id = null)
	{
//		dd((int) \v2\Classes\AdminCheck::check18() + 1);
//		if (! \v2\Classes\AdminCheck::check18() || ($type == 'entry' && \v2\Classes\AdminCheck::isBanned($entity))) {
//			if ($type == 'entry') {
//				return [];
//			} else {
//				return '';
//			}
//		}

		if ($file == "") {
			if ($size == 'c') {
				$image = "/images/No Imagec.jpg";

				return $image;
			}
		}

		$entry_images = 'entry_images';

		$path = "";

		$file_parts = explode('.', $file);

		if (substr($file, 0, 7) == 'http://') {
			if ($size == 's') {
				$size = 't';
			}

			for ($i = 0; $i < count($file_parts) - 1; $i++) {
				$path .= $file_parts[$i];

				if ($i == count($file_parts) - 2) {
					$path .= $size;
				}

				$path .= '.';
			}
			return $path . end($file_parts);

		} else if ($file == '') {
			return;
		}

		if ($folder == 'cover') {
			$folder .= '/cover.' . $size;

			$path = '/entry_images/entries/' . $id . '/cover/_cover_' . $size . '.jpg';

			return $path;
		}
		if ($folder == 'cg') {
			$folder .= '/' . $size;
		}
		if ($folder == 'char') {
			$file_split = explode('.', $file);
			$path = '/entry_images/char/' . $char_id . '/' . $file_split[0] . '.jpg';

			return $path;
		}
		$path = '/entry_images/entries/' . $id . '/' . $folder . '/' . $file_parts[0] . $size . '.' . end($file_parts);

		return $path;
	}



	//register_shutdown_function('shutdown');
	//ini_set('max_execution_time', 1);

	function buildArrayTable($data, $functions = [])
	{
		$values = [];

		if ($functions) {
			$data->setInitialized(false);
			foreach ($functions as $function) {
				if (substr($function, 0, 3) == 'get') {
					$key = substr($function, 3);
					$values[] = [$key => $data->{$function}()];
				}
			}

			$data->setInitialized(true);
			$data = $values;
		}
		if (count($data) == 0) {
			$str = "<i>Empty array.</i>";
		} else {
			$str = '<table style="background:white;font-size:17px;border-bottom:0px solid #091a1f;" cellpadding="0" cellspacing="0">';
			foreach ($data as $key => $value) {
				$value = ! (is_int($value) || is_string($value)) ? $value : '&nbsp;' . $value . '&nbsp;';
				$str .= '<tr>
							<td style="background-color: transparent; color:#000;border:1px solid #091a1f;">&nbsp;' . $key . '&nbsp;</td>
							<td style="border:1px solid #091a1f;">' . d($value) . '</td>
						</tr>';
			}
			$str .= "</table>";

			return $str;
		}
	}

	function d($data)
	{
		if (is_null($data)) {
			$str = "<i>NULL</i>";
		} elseif ($data === "") {
			$str = "<i>Empty</i>";
		} elseif ($data === false) {
			$str = "<i>false</i>";
		} elseif ($data === 0) {
			$str = "<i>0</i>";
		} elseif ($data === []) {
			$str = "<i>[]</i>";
		} elseif (is_object($data)) {
			$objectFunctions = get_class_methods($data);
			$str = buildArrayTable($data, $objectFunctions);
		} elseif (is_array($data)) {
			$str = buildArrayTable($data);
		} elseif (is_resource($data)) {
			while ($arr = mysql_fetch_array($data)) {
				$data_array[] = $arr;
			}
			$str = d($data_array);
		} elseif (is_bool($data)) {
			$str = "<i>" . ($data ? "True" : "False") . "</i>";
		} else {
			$str = $data;
			$str = preg_replace("/\n/", "<br>\n", $str);
		}
		return $str;
	}

	function dnl($data, $var2 = 'NULL', $var3 = 'NULL')
	{
		echo d($data) . "<br>\n";

		if ($var2 !== 'NULL') {
			echo d($var2) . "<br>\n";
		}
		if ($var3 !== 'NULL') {
			echo d($var3) . "<br>\n";
		}
	}

	function dd($data = null, $var2 = 'NULL', $var3 = 'NULL')
	{
		if (! empty($GLOBALS['dd']) && $GLOBALS['dd'] === false || ! \v2\Classes\AdminCheck::checkForAdmin()) {
			return;
		}
		if (is_string($data)) {
			$data = (string) $data;
		} elseif (is_null($data)) {
			$data = '_null_';
		}

		echo dnl($data, $var2, $var3);
		exit;
	}

	function ddt($message = "")
	{
		echo "[" . date("Y/m/d H:i:s") . "]" . $message . "<br>\n";
	}

	function dd2($value1 = null, $value2 = null, $value3 = null, $value4 = null, $value5 = null, $dc = false)
	{
		if (! empty($GLOBALS['dd']) && $GLOBALS['dd'] === false) {
			return;
		}
		$values = [];
		$values[] = $value1 ?: null;
		$values[] = $value2 ?: null;
		$values[] = $value3 ?: null;
		$values[] = $value4 ?: null;
		$values[] = $value5 ?: null;

		foreach ($values as $key => $value) {
			if (is_array($value)) {
				continue;
			}
		}

		if ($dc) {
			print_r($value1 . '<br>');
			return;
		}

		if ($GLOBALS['source'] == 'ajax') {
			die();
		}
	}

	function dc($data = null, $var2 = 'NULL', $var3 = 'NULL')
	{
		echo dnl($data, $var2, $var3);
	}

	function shutdown()
	{
		$a=error_get_last();

		$return = [
			'type'      => $a['type'],
			'line'      => $a['line'],
			'file'      => $a['file'],
			'success'   => false,
		];

		$message = str_replace('Stack trace:', '', $a['message']);

		$lines = explode('#', $message);
		foreach ($lines as $key => $line) {
			$line = substr(str_replace('↵', '', $line), 0, strlen($line) - 1);
			$return = array_merge($return, [$line]);
		}

		if ($a==null) {
			echo "No errors";
		} else {
			echo json_encode($return);
			die();
		}
	}

	function request($key = 'all', $default = false)
	{
		if ($key == 'all') {
			return array_merge($_GET, $_POST, $_FILES);
		}
		if (isset($_POST[$key])) {
			return $_POST[$key];
		}
		if (isset($_GET[$key])) {
			return $_GET[$key];
		}
		if (isset($_FILES[$key])) {
			return $_FILES[$key];
		}
		
		return $default;
	}

	function ra(bool $return = false)
	{
		if (\v2\Classes\AdminCheck::checkForAdmin()) {
			$data = array_merge($_GET, $_POST, $_FILES);
			if ($return) {
				return $data;
			}
			dd($data);
		}
	}

	function requestForSql($string)
	{
		$var = request($string);

		if (! $var) {
			return false;
		}

		return saveForSql($var);
	}

	function saveForSql($string, $connection = null) {
		if (! $connection) {
			$connection = app('connection');
		}

		$string = mysqli_real_escape_string($connection, $string);

		$string = str_replace('#', '\#', $string);

		return $string;
	}

	function put_log($message)
	{
		$log = new Log();

		$message = saveForSql((string) $message);

		$log->setMessage($message);

		app('em')->persist($log);
		app('em')->flush($log);
	}

	function logQuery($query, $connection, $filter = '') {
		if (strpos(strtolower($query), strtolower($filter)) === false) {
			return;
		}

		mysqli_set_charset($connection,"utf8");

		mb_regex_encoding('UTF-8');
		mb_internal_encoding('UTF-8');

		$query = saveForSql((string) $query, $connection);
		$query = sprintf("INSERT INTO log (message) VALUES ('%s')", (string) $query);

		mysqli_query($connection, $query);
	}

	/**
	 * @param string $incomingString
	 * @return string
	 */
	function validateForQueryUse(string $incomingString): string
	{
		$string = strtolower($incomingString);
		if ((strpos($string, 'drop') !== false) && (strpos($string, 'table') !== false)) {
			return '';
		}
		if ((strpos($string, 'update') !== false) && (strpos($string, 'set') !== false)) {
			return '';
		}
		if ((strpos($string, 'insert') !== false) && (strpos($string, 'into') !== false)) {
			return '';
		}
		if ((strpos($string, 'create') !== false) && (strpos($string, 'table') !== false)) {
			return '';
		}
		if ((strpos($string, 'create') !== false) && (strpos($string, 'view') !== false)) {
			return '';
		}
		return $incomingString;
	}

	/**
	 * Loads environment variables from a .env file.
	 */
	function loadEnv($path) {
		if (!file_exists($path)) return;

		$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		foreach ($lines as $line) {
			if (strpos(trim($line), '#') === 0) continue;

			list($key, $value) = explode('=', $line, 2);
			$key = trim($key);
			$value = trim($value);

			if (!array_key_exists($key, $_ENV)) {
				putenv("$key=$value");
				$_ENV[$key] = $value;
				$_SERVER[$key] = $value;
			}
		}
	}

	function watchIp($ipAddress, $developerId) {
		$bannedRepo = app('em')->getRepository(\v2\Database\Entity\Banned::class);
		$downloads = $bannedRepo->findByIpAndDeveloper($ipAddress, $developerId);

		return count($downloads);
	}
?>






