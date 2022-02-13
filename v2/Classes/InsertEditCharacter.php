<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 5-4-20
	 * Time: 16:38
	 */

	namespace v2\Classes;

	use v2\Database\Entity\Character;
	use v2\Manager;


	class InsertEditCharacter extends TextHandler
	{
		/**
		 * @var Character|null
		 */
		private $character = null;

		private $entryId = 0;

		/**
		 * Home constructor.
		 * @param $character
		 */
		public function __construct($character = null, $entryId = 0)
		{
			$this->entryId = $entryId;

			if ($character) {
				$this->character = $character;
			} else {
				$this->character = new Character();
			}

			$file = fopen(Manager::TEMPLATE_FOLDER . 'InsertEditCharacter.html', 'r');
			$this->content = fread($file, 10000);

			$this->cssFiles = [
				'InsertEdit'
			];

			$this->jsFiles = [
				'Home',
			];
		}

		public function buildContent()
		{
			$characterId = $this->character ? $this->character->getId() : 0;
			$action = $characterId ? 'editCharacter&cid=' . $this->character->getId() :	'insertCharacter';

			$action .= '&id=' . $this->entryId;

			$this->placeHolders = [
				'id'        => $characterId,
				'entryId'   => $this->entryId,
				'action'    => $action,
				'age'       => $characterId ? $this->character->getAge() : '',
				'name'      => $characterId ? $this->character->getName() : '',
				'romanji'   => $characterId ? $this->character->getRomanji() : '',
				'image'     => $characterId ? $this->getImage() : '',
				'gender'    => $characterId ? $this->character->getGender() : '',
				'height'    => $characterId ? $this->character->getHeight() : '',
				'weight'    => $characterId ? $this->character->getWeight() : '',
				'bust'      => $characterId ? $this->character->getBust() : '',
				'cup'       => $characterId ? $this->character->getCup() : '',
				'hips'      => $characterId ? $this->character->getHips() : '',
				'waist'     => $characterId ? $this->character->getWaist() : '',
			];

			$this->ifs = [
				'images'    => $this->getImages(),
			];

			$this->fillIfs();
			$this->fillPlaceHolders();
		}


		private function getImage()
		{
			return '_img';
		}

		private function getImages()
		{
			return [];
		}
	}