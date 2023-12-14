<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 10-2-20
	 * Time: 18:53
	 */

	namespace v2\Classes;


	use v2\Manager;
    use v2\Traits\TextHandler;

    class Home
	{
        use TextHandler;

		/**
		 * Home constructor.
		 * @param $character
		 */
		public function __construct()
		{
			$file = fopen(Manager::TEMPLATE_FOLDER . 'Home.html', 'r');
			$this->content = fread($file, 10000);

			$this->cssFiles = [
				'Item',
				'Home',
			];

			$this->jsFiles = [
				'Home',
			];
		}

		public function buildContent()
		{
			$this->placeHolders = [
				'navigator' => $this->getNavigator(),
			];

			$admin = AdminCheck::checkForAdmin();
			$local = AdminCheck::checkForLocal();

			$this->ifs = [
				'online'   => $admin && ! $local,
				'local'    => $admin && $local,
			];

			$this->fillIfs();
			$this->fillPlaceHolders();
		}

		private function getNavigator()
		{
			$navigator = new Navigator('home');

			$navigator->buildContent();

			return $navigator->getContent();
		}
	}