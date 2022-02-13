<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 8-12-19
	 * Time: 19:30
	 */

	namespace v2\Classes;

	use v2\Manager;

	class Borders extends TextHandler
	{
		public function buildContent()
		{
			$file = fopen(Manager::TEMPLATE_FOLDER . 'Borders.html', 'r');
			$this->content = fread($file, 10000);

			$this->fillPlaceHolders();
		}
	}