<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 1-12-19
	 * Time: 23:59
	 */

	namespace v2\Classes;

	loadEnv(str_replace('Classes', '', __DIR__) . '.env');

	use v2\Database\Entity\Banned;
	use v2\Database\Repository\BannedRepository;

	class AdminCheck
	{
		CONST ADULT = '';

		public static function get_ip_address()
		{
			$ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
			foreach ($ip_keys as $key) {
				if (array_key_exists($key, $_SERVER) === true) {
					foreach (explode(',', $_SERVER[$key]) as $ip) {
						// trim for safety measures
						$ip = trim($ip);
						// attempt to validate IP
						if (self::validate_ip($ip)) {
							return $ip;
						}
					}
				}
			}
			return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
		}

		private static function validate_ip($ip)
		{
			if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
				return false;
			}
			return true;
		}


		private static function checkLogin($ip) {
			if (! empty($_SESSION) &&
				(isset($_SESSION['ip']) && $ip == $_SESSION['ip']) &&
				time() - $_SESSION['code_time'] < getenv('SESSION_TIMEOUT_TIME') && $_SESSION['name'] == getenv('SESSION_NAME')) {
				return true;
			}
			return false;
		}

		public static function checkForAdmin()
		{
			$ip = self::get_ip_address();
			$allowedIps = explode(', ', getenv('IP_V4_ADDRESSES'));
			if (self::checkLogin($ip) ||
				in_array($ip, $allowedIps, true) ||
				(strpos($ip, getenv('IP_V6_MATCH')) !== false) ||
                		file_exists(getenv('ADMIN_FILE'))) {
				return true;
			} else {
				return false;
			}
		}

		public static function checkForLocal()
		{
			if (strpos($_SERVER['HTTP_HOST'], getenv('SITE_NAME')) !== false) {
				return false;
			} else {
				return true;
			}
		}

		public static function isBanned($entry = null)
		{
			if (isset($_SESSION['banned']) && $_SESSION['banned']) {
				return true;
			}

			if (self::checkForAdmin()) {
				return false;
			}

			$ip = self::get_ip_address();

			/** @var BannedRepository $bannedRepository */
			$bannedRepository = app('em')->getRepository(Banned::class);
			if ($bannedRepository->findBy(['ip' => $ip])) {
				return true;
			}

			if ($entry) {
				$ctx = stream_context_create(['http' =>
					[
						'timeout' => 2,  //2 Seconds
					],
				]);

				$location = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json", false, $ctx));
				if (isset($location->city)) {
					$location = $location->city;
				} else {
					$ctx2 = stream_context_create(['http' => [
						'timeout' => 2,  //2 Seconds
					],
					]);

					$location = json_decode(file_get_contents("http://freegeoip.net/json/{$ip}", false, $ctx2));

					if (isset($location->city)) {
						$location = $location->region_name;
					}
				}

				$banned = $bannedRepository->findByIpOrEntryAndLocation($entry, $ip, $location);
			}

			if (isset($banned) && count($banned) > 0) {
				//the user is found in the banned list
				return true;
			} else {
				return false;
			}
		}

		public static function check18()
		{
			return ! (isset($_SESSION['_18']) && $_SESSION['_18'] == '-');
		}

	}
