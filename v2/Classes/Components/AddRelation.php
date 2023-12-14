<?php

use v2\Traits\TextHandler;

/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 28-2-20
	 * Time: 23:08
	 */

	class AddRelation
	{
        use TextHandler;

		/**
		 * @var int
		 */
		private $nr;

		/**
		 * @var array
		 */
		private $entries = [];

		/**
		 * @var array
		 */
		private $relationTypes = [];


		public function __construct($nr, $type)
		{
			$this->nr = $nr;

			/** @var \v2\Database\Repository\EntryRepository $entryRepository */
			$entryRepository = app('em')->getRepository(\v2\Database\Entity\Entry::class);
			$entries = $entryRepository->findEntriesForAdding($type);

			$this->getEntries($entries);
			$this->relationTypes();

			$file = fopen(\v2\Manager::COMPONENT_FOLDER . 'AddRelation.html', 'r');
			$this->content = fread($file, 100000);
		}

		public function buildContent()
		{
			$this->placeHolders = [
				'nrUp'  => $this->nr + 1,
				'nr'    => $this->nr,
			];

			$this->fors = [
				'entries' => $this->entries,
				'relationTypes'     => $this->relationTypes,
			];

			$this->fillPlaceHolders();
			$this->fillFors();
		}

		private function getEntries($entries)
		{
			array_map(function ($entry) {
				$this->entries[] = [
					'value' => $entry->getId(),
					'title' => $entry->getRomanji() ?: $entry->getTitle(),
				];
			}, $entries);
		}

		private function relationTypes()
		{
			$this->relationTypes[] = ['selected' => true, 'value' => 'series','content' => 'Series'];
			$this->relationTypes[] = ['selected' => false, 'value' => 'prequel','content' => 'Sequel'];
			$this->relationTypes[] = ['selected' => false, 'value' => 'sequel','content' => 'Prequel'];
			$this->relationTypes[] = ['selected' => false, 'value' => 'pack','content' => 'Includes'];
			$this->relationTypes[] = ['selected' => false, 'value' => 'includes','content' => 'Pack'];
			$this->relationTypes[] = ['selected' => false, 'value' => 'parent story','content' => 'Side story'];
			$this->relationTypes[] = ['selected' => false, 'value' => 'side story','content' => 'Parent story'];
			return;
		}
	}