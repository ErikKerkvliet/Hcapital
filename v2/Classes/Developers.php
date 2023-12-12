<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 3-2-20
	 * Time: 23:24
	 */

	namespace v2\Classes;

	use v2\Manager;

	class Developers extends TextHandler
	{
		/**
		 * @var array
		 */
		private $items = [];

		/**
		 * @var float|int
		 */
		private $itemCount = 75;

		/**
		 * GameList constructor.
		 * @param $items
		 */
		public function __construct($items)
		{
			$this->items = $items;

			$file = fopen(Manager::TEMPLATE_FOLDER . 'Developers.html', 'r');
			$this->content = fread($file, 100000);

			$this->cssFiles = [
				'info',
			];

			$this->jsFiles = [
				'DeveloperList',
			];

			$this->itemCount = count($items) === 75 ? 25 : ceil(count($items) / 3);
		}

		/**
		 * Setup all the page info
		 */
		public function buildContent()
		{
			$this->fors = [
				'developers1'    => $this->getDevelopers(),
				'developers2'    => $this->getDevelopers(),
				'developers3'    => $this->getDevelopers(),
			];

			$this->fillFors();
		}

		private function getDevelopers()
		{
			$developers = [];
			$times = 0;

			for ($i = 0; $i < 25; $i++) {
				if ($this->itemCount <= $i) {
					$developers[] = [
						'background' => $times % 2,
						'developer'  => '',
						'href'  => '',
					];
					$times++;
					continue;
				}
				if (isset($this->items[$i])) {
					$developer = $this->items[$i];
					$developers[] = [
						'background' => $times % 2,
						'developer' => $developer->getName(),
						'developerColor' => $developer->getType() == 'ova' ? 1 : ($developer->getType() == '3d' ? 2 : 0),
						'id' => $developer->getId(),
						'href'  => "href=\"?v=2&did=" . ($developer->getId() ?: '') . "\"",
					];
				} else {
					$developers[] = [
						'background' => $times % 2,
						'developer'  => '',
						'href'  => '',
					];
				}
				$times++;

				unset($this->items[$i]);

				if ($times == 25) {
					break;
				}
			}
			$this->items = array_values($this->items);

			return $developers;
		}
	}