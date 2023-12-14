<?php

use v2\Database\Entity\Host;
use v2\Traits\TextHandler;

/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 29-2-20
	 * Time: 0:00
	 */

	class AddLink
	{
        use TextHandler;

		/**
		 * @var int
		 */
		private $nr;

		/**
		 * @var array
		 */
		private $hosts = [];

		/**
		 * @var array
		 */
		private $links = [];

		/**
		 * @var string
		 */
		private $comment = '';

		public function __construct($nr, $links = [])
		{
			$this->nr = $nr;
			$this->links = $links;
			$this->comment = ($keys = array_keys($this->links)) ? $keys[0] : '';
			$file = fopen(\v2\Manager::COMPONENT_FOLDER . 'AddLink.html', 'r');
			$this->content = fread($file, 100000);
		}

		public function buildContent()
		{
			$this->placeHolders = [
				'comment-nr'    => $this->nr,
				'comment' => $this->comment,
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
			$this->nr *= count(Host::HOSTS);
			foreach (Host::HOSTS as $host) {
				$this->hosts[] = [
					'label' => ucfirst($host),
					'host' => $host,
					'nr' => $this->nr,
					'links' => isset($this->links[$this->comment]) && isset($this->links[$this->comment][$host])
						? implode('splitter', $this->links[$this->comment][$host])
						: '',
				];
				$this->nr++;
			}
		}
	}