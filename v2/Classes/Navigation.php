<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 22-1-20
	 * Time: 12:16
	 */

	namespace v2\Classes;


	use v2\Manager;

	class Navigation extends TextHandler
	{
		/**
		 * Navigation constructor.
		 */
		public function __construct()
		{
			$file = fopen(Manager::TEMPLATE_FOLDER . 'Navigation.html', 'r');
			$this->content = fread($file, 100000);
		}

		/**
		 *
		 */
		public function buildContent()
		{
			$this->fors = [
				'navigation' => [['text' => 'previous'], ['text' => 'next']],
			];

			$this->fillFors();
		}
	}