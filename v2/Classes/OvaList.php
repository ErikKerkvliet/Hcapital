<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 18-1-20
	 * Time: 12:14
	 */

	namespace v2\Classes;


	use v2\Manager;
    use v2\Traits\TextHandler;

    class OvaList
	{
        use TextHandler;

		/**
		 * @var array|null
		 */
		protected $items = [];

		/**
		 * @var array
		 */
		private $series = [];

		/**
		 * @var bool|string
		 */
		private $originalContent = '';


		public function __construct($items)
		{
			$this->items = $items;

			$file = fopen(Manager::TEMPLATE_FOLDER . 'EntryList.html', 'r');
			$this->content = fread($file, 100000);
			$this->originalContent = $this->content;

			$this->cssFiles = [
				'info',
			];

			$this->jsFiles = [
				'info',
				'showlinks',
			];
		}

		/**
		 * Setup all the page info
		 */
		public function buildContent()
		{
			$content = '';

			$this->createSeries();

			foreach ($this->series as $series) {
				$tmp = [];
				$series = array_filter($series, function ($episode) use (&$tmp) {
					if (! in_array($episode->getId(), $tmp)) {
						$tmp[] = $episode->getId();
						return true;
					}
					return false;
				});

				$entry = $series[0]->getEntry() ?: $series[0]->getId();

				$this->content = $this->originalContent;

				$this->placeHolders = [
					'id'        => $series[0]->getId(),
					'cover'     => getImages($entry, 'cover'),
					'released'  => $series[0]->getReleased(),
					'title'     => $this->getTitle($series[0], 'main'),
					'romanji'   => $series[0]->getRomanji(),
					'type'      => 'ova',
				];

				$this->ifs = [
					'series'    => true,
					'game'      => false,
				];

				$this->fors = [
					'episodes' => $this->getEpisodes($series),
				];

				$this->fillIfs();
				$this->fillFors();
				$this->fillPlaceHolders();

				$content .= $this->content;
			}
			$this->content = $content;
		}

		private function getEpisodes($series)
		{
			$placeHolder = [];
			foreach ($series as $key => $episode) {
				$entry = $episode->getEntry() ?: $episode->getId();

				$placeHolder[] = [
					'id'        => $entry,
					'cover'     => getImages($entry, 'cover'),
					'title'     => $this->getTitle($episode),
					'released'  => $episode->getReleased(),
				];
			}
			return $placeHolder;
		}

		private function createSeries()
		{
			array_map(function ($item) {
				if ($item->getRelation() !== null) {
					$this->series[$item->getRelation()][] = $item;
				} else {
					$this->series[$item->getId()][] = $item;
				}
			}, $this->items);

			foreach($this->series as &$episodes) {
				usort($episodes, function($a, $b) {
					return $a->getReleased() <=> $b->getReleased();
				});
			}
		}

		private function getTitle($episode, $type = 'little')
		{
			$title = $episode->getTitle();
			$strpos = strpos($title, ' Vol.');
			if ($strpos == 0) {
				return $title;
			}
			if ($type != 'little') {
				return substr($title, 0, $strpos);
			}
			return substr($title, $strpos);
		}
	}