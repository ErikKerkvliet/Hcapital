<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 8-12-19
	 * Time: 19:55
	 */

	namespace v2\Classes;

	use v2\Manager;

	class Footer extends TextHandler
	{
		public function buildContent()
		{
			$file = fopen(Manager::TEMPLATE_FOLDER . 'Footer.html', 'r');
			$this->content = fread($file, 10000);

			$this->placeHolders = [
				'year'  => date("Y"),
			];

			$this->fillPlaceHolders();
		}
	}