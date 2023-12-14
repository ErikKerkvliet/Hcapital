<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 19-10-23
	 * Time: 15:18
	 */

use v2\Traits\TextHandler;

class IpData
	{
        use TextHandler;

		private $ipData = [];

		public function __construct($ipData)
		{
			$this->ipData = $ipData;

			$file = fopen(\v2\Manager::COMPONENT_FOLDER . 'IpData.html', 'r');
			$this->content = fread($file, 100000);
		}

		public function buildContent()
		{
			$this->fors = [
				'ipData' => $this->getIpData(),
			];

			$this->fillFors();
		}

		private function getIpData()
		{
			$data = [];
			$row = 0;
			$showData = ['ip', 'country_name', 'region', 'city', 'postal', 'org'];
			foreach ($this->ipData as $key => $value) {
				if (! in_array($key, $showData)) {
					continue;
				}

				$data[] = [
					'tr' => 'row-color-' . ($row % 2),
					'label' => str_replace('_', ' ', $key),
					'value' => $value,
				];
				$row++;
			}

			return $data;
		}
	}