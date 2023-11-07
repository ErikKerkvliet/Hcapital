<?php

	use v2\Database\Entity\Host;

	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 29-2-20
	 * Time: 0:00
	 */

	class AddLink extends \v2\Classes\TextHandler
	{
		/**
		 * @var int
		 */
		private $nr;

		/**
		 * @var array
		 */
		private $hosts = [];

		public function __construct($nr)
		{
			$this->nr = $nr;

			$file = fopen(\v2\Manager::COMPONENT_FOLDER . 'AddLink.html', 'r');
			$this->content = fread($file, 100000);
		}

		public function buildContent()
		{
			$this->placeHolders = [
				'comment-nr'    => $this->nr,
			];

			$this->setHosts();

			$this->fors = [
				'hosts' => $this->hosts,
			];

			$this->fillPlaceHolders();
			$this->fillFors();
		}

		private function setHosts()
		{
			$this->nr *= 3;
			foreach (Host::HOSTS as $host) {
				$this->hosts[] = [
					'label' => ucfirst($host),
					'host' => $host,
					'nr' => $this->nr,
				];
				$this->nr++;
			}
		}
	}