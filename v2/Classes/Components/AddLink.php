<?php
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
				'nr'    => ($this->nr * 2),
				'nrUp'  => ($this->nr * 2) + 1,
			];

			$this->fillPlaceHolders();
		}
	}