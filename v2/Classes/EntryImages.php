<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 1-1-20
	 * Time: 2:31
	 */

	namespace v2\Classes;


	use v2\Manager;

	class EntryImages extends TextHandler
	{
		/**
		 * @var null
		 */
		private $entry = null;

		/**
		 * @var array
		 */
		private $images = [];

		/**
		 * Images constructor.
		 * @param $entry
		 */
		public function __construct($entry)
		{
			$this->entry = $entry;
			$this->images = getImages($this->entry, 'entry');
		}

		public function buildContent()
		{
			$file = fopen(Manager::TEMPLATE_FOLDER . 'EntryImages.html', 'r');
			$this->content = fread($file, 100000);

			$this->placeHolders = [
				'image'    => getImages(1234,'cover'),
			];
			$this->fors =[
				'images' => $this->getSingleRowImages(),
			];

			$this->fillPlaceHolders();
			$this->fillFors();
		}

		private function getSingleRowImages()
		{
			$images = [];
			for ($i = 0; $i < 5; $i++) {
				$images[] = [
					'image' => $this->images[$i],
				];
			}
			return $images;
		}
	}