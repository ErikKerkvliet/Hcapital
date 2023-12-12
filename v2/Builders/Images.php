<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 29-12-19
	 * Time: 13:39
	 */

	namespace v2\Builders;


	use v2\Classes\TextHandler;
	use v2\Manager;

	class Images
	{
		/**
		 * @var null
		 */
		private $entry = null;

		/**
		 * @var string
		 */
		private $html = '';

		/**
		 * @var string
		 */
		private $path = '';

		/**
		 * @var array
		 */
		private $images = [];

		/**
		 * @var int
		 */
		private $imageId = 1;

		/**
		 * Images constructor.
		 * @param $entry
		 */
		public function __construct($entry)
		{
			$this->entry = $entry;
			$this->images = getImages($this->entry, 'entry');
			$this->path = 'entry_images/entries/' . $this->entry->getId() . '/cg/';
		}

		public function createImages($singleRow = false)
		{
			$singleRow ? $this->createMoreButton() : null;

			$this->html .= $singleRow ? '<div class="image-rows"  id="single">' :
				'<div class="image-rows" id="multiple">';

			$this->html .= '<table class="image-table">';

			$i = 1;
			foreach ($this->images as $key => $image) {
				if ($singleRow && $key == 5) {
					break;
				}
				$this->html .= $i == 0 ? '<tr>' : '';

				$this->html .= '<td>';

				$this->createSingleImage($image);

				$this->html .= '</td>';
				$this->html .= $i == 5 ? '</tr>' : '';

				$i = $i == 5 ? 1 : $i + 1;
			}
			$this->html .= '</table>';
			$this->html .= '</div>';

			return $this->html;
		}

		private function createMoreButton()
		{
			if (count($this->images) > 5) {
				$this->html .= '<br>';
				$this->html .= '<div id="show-more">';
				$this->html .= '+ more images';
				$this->html .= '</div>';
			}
		}

		private function createSingleImage($image)
		{
			$this->html .= '<div class="div_hide" id="table_td">';
			$this->html .= '<div class="div_img" id="div_img"  image-id="' . $this->imageId . '" style="display:none;">';
			$this->html .= '<img id="img" src="' . $this->path . $image . '"/>';

			$this->createArrow('previous');
			$this->createArrow('next');

			$this->html .= '<div class="pop-up-img-close">X</div>';
			$this->html .= '</div>';

			$this->createPreviewImage($image);

			$this->html .= '</div>';
			$this->imageId++;
		}

		private function createArrow($side)
		{
			$arrowText = $side == 'previous' ? '≪' : '≫';

			$this->html .= '<div class="arrow-box" id="arrow-' . $side . '">';
			$this->html .= '<div>' . $arrowText . '</div>';
			$this->html .= '<div class="arrow-button" id="button-' . $side . '"/>';
			$this->html .= '</div>';
			$this->html .= '</div>';
		}

		private function createPreviewImage($image)
		{
			$this->html .= '<div class="static_img" id="smallImage">';
			$this->html .= '<img class="info_images" src="' . $this->path . $image . '"/>';
			$this->html .= '</div>';
		}
	}