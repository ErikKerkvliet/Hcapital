<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 1-12-19
	 * Time: 23:59
	 */

	namespace v2\Classes;

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
				time() - $_SESSION['code_time'] < 3600 && $_SESSION['name'] == 'yuuichi') {
				return true;
			}
			return false;
		}

		public static function checkForAdmin()
		{
			$ip = self::get_ip_address();

			if (self::checkLogin($ip) ||
				$ip == '77.167.87.187' ||
				$ip == '::1' ||
				(strpos($ip, '2a01:7c8:aabc:2b5') !== false) ||
                file_exists('./check.txt')) {
				return true;
			} else {
				return false;
			}
		}

		public static function checkForLocal()
		{
			if (strpos($_SERVER['HTTP_HOST'], 'hcapital.tk') !== false) {
				return false;
			} else {
				return true;
			}
		}

		public static function isBanned($entry = null)
		{
			if (self::checkForAdmin()) {
				return false;
			}

			$ip = self::get_ip_address();

			/** @var BannedRepository $bannedRepository */
			$bannedRepository = app('em')->getRepository(Banned::class);

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
			} else {
				$banned = $bannedRepository->findBy(['ip' => $ip]);
			}

			if (count($banned) > 0) {
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