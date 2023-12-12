<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 16-12-19
	 * Time: 19:33
	 */

	namespace v2\Classes;

	use v2\Database\Entity\Entry;
	use v2\Database\Entity\EntryRelation;
	use v2\Database\Repository\EntryRelationRepository;
	use v2\Manager;

	class EntryRelations extends TextHandler
	{
		private $entry = null;

		public function __construct($entry)
		{
			$this->entry = $entry;
		}

		/**
		 * Build the content of the relation part
		 */
		public function buildContent()
		{
			$file = fopen(Manager::TEMPLATE_FOLDER . 'EntryRelations.html', 'r');
			$this->content = fread($file, 10000);

			$this->fors = [
				'relations' => $this->getRelations(),
			];
			$this->ifs = [
				'series'        => $this->entry->getType() == 'ova' ? 'true' : null,
				'not_series'    => $this->entry->getType() == 'ova' ? null : 'ova',
			];

			$this->fillFors();
			$this->fillIfs();
			$this->fillPlaceHolders();
		}

		/**
		 * @return array
		 */
		private function getRelations(): array
		{
			/** @var EntryRelationRepository $relationRepository */
			$relationRepository = app('em')->getRepository(EntryRelation::class);

			$relationEntities = $relationRepository->findById($this->entry->getId());

			$relations = [];

			/** @var EntryRelation $relation */
			foreach ($relationEntities as $relation) {
				array_push($relations, [
					'id'            => $relation[0]->getId(),
					'title'         => $this->getTitle($relation[0]),
					'released'      => $relation[0]->getReleased(),
					'cover'         => '../../../entry_images/entries/' . $relation[0]->getId() . '/cover/' . $this->getCover($relation[0]),
					'type'  => ucfirst($relation[1]),
				]);
			}
			return $relations;
		}

		/**
		 * @param Entry $relation
		 * @param string $size
		 * @return string
		 */
		private function getCover(Entry $relation, $size = 'm'): string
		{
			if (isset($_SESSION['_18']) && $_SESSION['_18'] == '-') {
				return 'images/No Image' . $size . '.jpg';
			}
			return $size == 'm' ? $relation::COVER_M : $relation::COVER_L;
		}

		/**
		 * @param Entry $entry
		 * @return string
		 */
		private function getTitle(Entry $entry): string
		{
			$title = $entry->getTitle();
			if ($entry->getType() == 'game') {
				return $title;
			}

			if ($entry->getType() == '3d' && (strpos($title, 'Vol. ') === false)) {
				$title = substr($title, 0, -7);
			} else if ((strpos($title, 'Vol. ') !== false)) {
				$title_parts = explode("Vol. ", $title);

				$title = "Vol. " . $title_parts[1];
			}
			return $title;
		}
	}