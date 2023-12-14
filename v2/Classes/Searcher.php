<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 16-1-20
	 * Time: 19:01
	 */

	namespace v2\Classes;


	use v2\Manager;
    use v2\Traits\TextHandler;

    class Searcher
	{
        use TextHandler;

		private $type = '';

		public function __construct($type)
		{
			$this->type = $type ?: 'all';

			$file = fopen(Manager::TEMPLATE_FOLDER . 'Searcher.html', 'r');
			$this->content = fread($file, 100000);

			$this->cssFiles = [
				'info',
			];

			$this->jsFiles = [
				'Searcher'
			];
		}

		public function buildContent()
		{
			$this->fors = [
				'searchCharacters' => $this->getAsciiCharacters(),
				'tabs'             => $this->getTabData(),
			];

			$this->fillFors();
		}

		private function getAsciiCharacters()
		{
			$characters = [];

			for ($i = 65; $i < 91; $i++) {
				$characters[] = ['char' => chr($i)];
			}
			return $characters;
		}

		private function getTabData()
		{
			$tabs = [];

			$tabs[] = ['type' => 'all', 'text' => 'All', 'class' => $this->getSelected('all')];
			$tabs[] = ['type' => 'game', 'text' => 'Games', 'class' => $this->getSelected('game')];
			$tabs[] = ['type' => 'ova', 'text' => 'OVA\'s', 'class' => $this->getSelected('ova')];
			$tabs[] = ['type' => '3d', 'text' => '3D', 'class' => $this->getSelected('3d')];
			$tabs[] = ['type' => 'developer', 'text' => 'Developers', 'class' => $this->getSelected('developer')];
			$tabs[] = ['type' => 'character', 'text' => 'Characters', 'class' => $this->getSelected('character')];

			return $tabs;
		}

		private function getSelected($type)
		{
			if ($this->type == $type) {
				return 'selected';
			}
			return '';
		}
	}