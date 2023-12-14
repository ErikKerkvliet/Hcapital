<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 19-3-20
	 * Time: 21:05
	 */

	namespace v2\Classes;


	use v2\Traits\TextHandler;

    class Import
	{
        use TextHandler;

		public function __construct()
		{
			$file = fopen(\v2\Manager::TEMPLATE_FOLDER . 'Import.html', 'r');
			$this->content = fread($file, 100000);

			$this->cssFiles = [];

			$this->jsFiles = [];
		}

		public function buildContent()
		{
		}
	}