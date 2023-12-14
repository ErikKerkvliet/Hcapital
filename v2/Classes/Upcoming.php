<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 15-2-20
	 * Time: 21:53
	 */

	namespace v2\Classes;


	use v2\Manager;
    use v2\Traits\TextHandler;

    class Upcoming
	{
        use TextHandler;

		public function __construct()
		{
			$this->cssFiles = [
				'Home',
				'Item',
			];

			$this->jsFiles = [
				'Home'
			];
		}

		public function buildContent()
		{
			$file = fopen(Manager::TEMPLATE_FOLDER . 'Upcoming.html', 'r');
			$this->content = fread($file, 10000);

			$this->placeHolders = [
				'navigator' => $this->getNavigator(),
			];

			$this->fillPlaceHolders();
		}

		private function getNavigator()
		{
			$navigator = new Navigator('upcoming');

			$navigator->buildContent();

			return $navigator->getContent();
		}

	}