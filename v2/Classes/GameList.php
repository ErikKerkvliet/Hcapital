<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 11-1-20
	 * Time: 16:53
	 */

	namespace v2\Classes;


	use v2\Database\Entity\Entry;
    use v2\Manager;
    use v2\Traits\TextHandler;

    class GameList
	{
        use TextHandler;

		/**
		 * @var Entry|null
		 */
		protected $items = [];

		/**
		 * @var bool|string
		 */
		private $originalContent = '';


		/**
		 * GameList constructor.
		 * @param $items
		 */
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
			foreach ($this->items as $entry) {
				$this->content = $this->originalContent;

				$this->ifs = [
					'website'   => $entry->getWebsite(),
					'cover'     => getImages($entry, 'cover'),
					'series'    => false,
					'game'      => true,
				];

				$this->placeHolders = [
					'id'        => $entry->getId(),
					'url'       => $entry->getId(),
					'cover'     => getImages($entry, 'cover'),
					'released'  => $entry->getReleased(),
					'title'     => $entry->getTitle(),
					'romanji'   => $entry->getRomanji(),
					'website'   => $entry->getWebsite(),
					'type'      => 'game',
				];

				$this->fillFors();
				$this->fillIfs();
				$this->fillPlaceHolders();

				$content .= $this->content;
			}

			$this->content = $content;
		}
	}