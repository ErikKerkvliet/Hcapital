<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 21-1-20
	 * Time: 10:58
	 */

	namespace v2\Classes;


	use v2\Manager;

	class OrderBar extends TextHandler
	{
		private $type;

		public function __construct($type)
		{
			$this->type = $type;

			$file = fopen(Manager::TEMPLATE_FOLDER . 'OrderBar.html', 'r');
			$this->content = fread($file, 100000);
		}

		/**
		 * Setup all the page info
		 */
		public function buildContent()
		{
			$this->ifs = [
				'entry' => $this->getOrder('entry'),
				'character' => $this->getOrder('character'),
			];

			$this->fillFors();
			$this->fillIfs();
			$this->fillPlaceHolders();
		}

		private function getOrder($order)
		{
			if (($this->type != 'character' && $order != 'character') ||
				($order == 'character' && $this->type == 'character')) {
				return true;
			}
			return false;
		}
	}