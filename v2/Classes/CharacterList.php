<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 24-1-20
	 * Time: 15:23
	 */

	namespace v2\Classes;


	use v2\Manager;
    use v2\Traits\TextHandler;

    class CharacterList
	{
        use TextHandler;

		private $items = [];

		private $originalContent = '';

		/**
		 * GameList constructor.
		 * @param $items
		 */
		public function __construct($items)
		{
			$this->items = $items;
			$file = fopen(Manager::TEMPLATE_FOLDER . 'CharacterList.html', 'r');
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
			foreach ($this->items as $char) {
				$this->content = $this->originalContent;

				$this->placeHolders = [
					'id'        => $char->getId(),
					'tumbnail'  => getImages($char, 'char', 'tumbnail'),
					'name'      => $char->getName(),
					'romanji'   => $char->getRomanji(),
					'gender'    => $char->getGender(),
				];

				$this->fillFors();
				$this->fillIfs();
				$this->fillPlaceHolders();

				$content .= $this->content;
			}

			$this->content = $content;
		}
	}