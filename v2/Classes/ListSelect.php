<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 1-2-20
	 * Time: 22:15
	 */

	namespace v2\Classes;


	use v2\Manager;
    use v2\Traits\TextHandler;

    class ListSelect
	{
        use TextHandler;

		private $buttonTypes = [];

		private $type;

		public function __construct($buttonTypes)
		{
			$this->buttonTypes = $buttonTypes;

			$file = fopen(Manager::TEMPLATE_FOLDER . 'ListSelect.html', 'r');
			$this->content = fread($file, 100000);

			$this->cssFiles = [];

			$this->jsFiles = [];
		}

		/**
		 * Setup all the page info
		 */
		public function buildContent()
		{
			$this->placeHolders = [
				'search'    => request('s'),
				'searcher'  => $this->getSearcher(),
			];

			$this->ifs = [
				'games' => in_array('game', $this->buttonTypes),
				'ovas' => in_array('ova', $this->buttonTypes),
				'3d' => in_array('3d', $this->buttonTypes),
				'developers' => in_array('developer', $this->buttonTypes),
				'characters' => in_array('character', $this->buttonTypes),
			];
			$this->fillPlaceHolders();
			$this->fillIfs();
		}

		private function getSearcher()
		{
			$searcher = new Searcher($this->type);

			$searcher->buildContent();

			return $searcher->getContent();
		}
	}