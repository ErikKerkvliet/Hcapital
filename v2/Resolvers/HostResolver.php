<?php

	use v2\Database\Entity\Host;

	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 7-11-23
	 * Time: 22:34
	 */

	class HostResolver
	{
		const DEFAULT_TYPE = 'download';

		public function byUrl($url, $capitalized = false)
		{
			$host = '';
			if (strpos($url, 'rapidgator.net') !== false) {
				$host = Host::HOST_RAPIDGATOR;
			}
			if (strpos($url, 'rg.to/') !== false) {
				$host = Host::HOST_RAPIDGATOR;
			}
			if (strpos($url, 'mexashare.com') !== false) {
				$host = Host::HOST_MEXASHARE;
			}
			if (strpos($url, 'mx-sh.net') !== false) {
				$host = Host::HOST_MEXASHARE;
			}
			if (strpos($url, 'mexa.sh') !== false) {
				$host = Host::HOST_MEXASHARE;
			}
			if (strpos($url, 'bigfile.to') !== false) {
				$host = Host::HOST_BIGFILE;
			}
			if (strpos($url, 'katfile.com') !== false) {
				$host = Host::HOST_KATFILE;
			}
			if (strpos($url, 'rosefile.net') !== false) {
				$host = Host::HOST_ROSEFILE;
			}
			if (strpos($url, 'ddownload.com') !== false) {
				$host = Host::HOST_DDOWNLOAD;
			}
			if (strpos($url, 'fikper.com') !== false) {
				$host = Host::HOST_FIKPER;
			}
			if (! $host) {
				$host = self::DEFAULT_TYPE;
			}
			return $capitalized ? ucfirst($host) : $host;
		}
	}
