<?php

	namespace v2\Resolvers;
	
	use v2\Database\Entity\Host;

	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 7-11-23
	 * Time: 22:34
	 */
	class HostResolver
	{
		const REGEXP_PATTERNS = [
			Host::HOST_RAPIDGATOR => [
				'rapidgator.net',
				'rg.to/',
			],
			Host::HOST_MEXASHARE => [
				'mexashare.com',
				'mx-sh.net',
				'mx-sh.net',
				'mexa.sh'
			],
			Host::HOST_BIGFILE => [
				'bigfile.to',
			],
			Host::HOST_KATFILE => [
				'katfile.com',
			],
			Host::HOST_ROSEFILE => [
				'rosefile.net',
			],
			Host::HOST_DDOWNLOAD => [
				'ddownload.com',
			],
			Host::HOST_FIKPER => [
				'fikper.com',
			],
		];

		const DEFAULT_TYPE = 'download';

		public function byUrl($url, $capitalized = false)
		{
			foreach (self::REGEXP_PATTERNS as $host => $patterns) {
				foreach ($patterns as $pattern) {
					if (strpos($url, $pattern) !== false) {
						return $capitalized ? ucfirst($host) : $host;
					}
				}
			}
			return $capitalized ? ucfirst(self::DEFAULT_TYPE) : self::DEFAULT_TYPE;
		}
	}
