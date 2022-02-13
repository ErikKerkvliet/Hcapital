<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 4-1-20
	 * Time: 0:29
	 */

	namespace v2\Builders;


	use v2\Database\Entity\Character;
	use v2\Database\Entity\EntryCharacter;
	use v2\Database\Entity\DeveloperRelation;
	use v2\Database\Repository\EntryCharacterRepository;
	use v2\Database\Repository\DeveloperRelationRepository;

	class Characters
	{
		/**
		 * @var array
		 */
		private $characters = [];

		/**
		 * @var Character|null
		 */
		private $character = null;

		/**
		 * @var string
		 */
		private $imagePath = '';

		private $rowNr = 0;

		private $placeHolders = [];

		private $fors = [];

		private $ifs = [];

		private $template = '';

		protected $entry = null;

		protected $html = '';

		public function __construct($entry)
		{
			$this->entry = $entry;

			$this->originalTemplate = $this->getGetTemplate();

			$this->template = $this->originalTemplate;
		}

		public function createCharacters()
		{

			/** @var EntryCharacterRepository $characterRepository */
			$characterRepository = app('em')->getRepository(EntryCharacter::class);

			$this->characters = $characterRepository->findCharactersByEntry($this->entry);

			count($this->characters) ? $this->createCharactersButton() : null;

			$this->html .= '<div id="characters-table">';
			foreach ($this->characters as $character) {
				$this->imagePath = './../entry_images/char/' . $character->getId();

				$this->character = $character;

				$this->createCharacter();
			}
			$this->html .=  '</div>';

			return $this->html;
		}

		private function createCharactersButton()
		{
			$this->html .= '<div id="show-chars">+ characters</div>';
		}

		/**
		 * @param Character $character
		 */
		private function createCharacter()
		{
			$this->placeHolders = [
				'id'            => $this->character->getId(),
				'romanji'       => $this->character->getRomanji(),
				'name'          => $this->character->getName(),
				'gender'        => $this->getGender(),
				'tumbnail'      => getImages($this->character, 'char', 'tumbnail'),
			];

			$this->ifs = [
				'age'           => $this->character->getAge(),
				'height'        => $this->character->getHeight(),
				'weight'        => $this->character->getWeight(),
				'waist'         => $this->character->getWaist(),
				'bust'          => $this->getBust(),
				'hips'          => $this->character->getHips(),
				'cup'           => $this->getBust() ? null : $this->character->getCup(),
			];

			$this->getRows();

			$images = [];
			foreach (getImages($this->character, 'char') as $image) {
				if (strpos($image, '/..') !== false) {
					continue;
				}
				$images[] = ['image' => $image];
			}

			$this->fors = [
				'images'    => $images,
			];

			$this->fillIfs();
			$this->fillPlaceHolders();
			$this->fillFors();

			$this->html .= $this->template;
			$this->template = $this->originalTemplate;
		}

		private function getGender()
		{
			$gender = $this->character->getGender();

			if ($gender == 'male') {
				return '&#9794;';
			}
			if ($gender == 'female') {
				return '&#9792;';
			}
			if ($gender == 'both') {
				return '&#9792;&#9794;';
			}
			return '&#9897;';
		}

		private function getBust()
		{
			if (($bust = $this->character->getBust()) && ($cup = $this->character->getCup())) {
				return $bust . ' cm (' . $cup . ')';
			}
			if ($bust = $this->character->getBust()) {
				return $bust;
			}
			return '';
		}

		private function getRows()
		{
			$this->placeHolders = array_merge($this->placeHolders, [
				'rowAge'           => $this->getRowNr($this->ifs['age']),
				'rowHeight'        => $this->getRowNr($this->ifs['height']),
				'rowWeight'        => $this->getRowNr($this->ifs['weight']),
				'rowBust'          => $this->getRowNr($this->ifs['bust']),
				'rowCup'           => $this->ifs['bust'] ? null : $this->getRowNr($this->ifs['cup']),
				'rowWaist'         => $this->getRowNr($this->ifs['waist']),
				'rowHips'          => $this->getRowNr($this->ifs['hips']),
			]);
			$this->rowNr = 0;
		}

		private function getRowNr($value)
		{
			if (! $value) {
				return null;
			}
			$this->rowNr++;

			return (string) ((int) ($this->rowNr % 2 == 1));
		}

		private function getGetTemplate()
		{
			$file = fopen('./v2/Templates/Characters.html', 'r');
			return fread($file, 10000);
		}

		private function fillPlaceHolders()
		{
			foreach ($this->placeHolders as $key => $placeHolder) {
				$this->template = str_replace('{{' . $key . '}}', $placeHolder, $this->template);
			}
		}

		private function fillFors()
		{
			foreach ($this->fors as $for => $values) {
				$text = '';
				$pattern = '/\{for ' . $for . '\}(.*?)\{\/for\}/s';

				preg_match($pattern, $this->template, $match);

				foreach ($values as $value) {
					$loopText = $match[1];

					foreach ($value as $key => $val) {
						$placeholder = '{{' . $key . '}}';
						$loopText = str_replace($placeholder, $val, $loopText);
					}

					$text .= $loopText;
				}
				$this->template = str_replace($match[0], $text, $this->template);
			}
			$this->template = str_replace('{/for}', '', $this->template);
		}

		protected function fillIfs()
		{
			foreach ($this->ifs as $if => $value) {
				$pattern = '/\{if ' . $if . '\}(.*?)\{\/if\}/s';

				preg_match_all($pattern, $this->template, $matches);

				if ($value == false && $value !== 0) {
					$this->template = str_replace($matches[0], '', $this->template);
				} else {
					$search = '{{' . $if . '}}';
					$this->template = str_replace($search, $this->ifs[$if], $this->template);
				}
			}
			$this->template = str_replace('{/if}', '', $this->template);
		}
	}