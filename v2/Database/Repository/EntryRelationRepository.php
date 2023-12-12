<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 16-12-19
	 * Time: 19:37
	 */

	namespace v2\Database\Repository;

	use v2\Database\Entity\Entry;
	use v2\Database\Entity\EntryRelation;
	use v2\Database\EntityManager;

	class EntryRelationRepository extends Repository
	{
		/**
		 * Entity class of repository
		 */
		protected $entity = EntryRelation::class;

		private $entryRelationColumns = [];

		public function __construct(EntityManager $em, $entityClass)
		{
			parent::__construct($em, $entityClass);

			$this->entryRelationColumns = [
				'r.id', 'r.entry_id', 'r.relation_id', 'r.type'
			];
		}

		public function findEntryByRelatedEntry($entry, $columns = [], $orderBy = [])
		{
			$id = ! is_object($entry) ? $entry : $entry->getId();

			if (! $columns) {
				$columns = $this->entryRelationColumns;
			} else {
				foreach ($columns as &$column) {
					$column = substr($column, 0, 2) == 'r.' ?: 'r.' . $column;
				}
			}

			$columns = ['r1.id', 'r1.entry_id', 'r1.relation_id', 'r1.type'];

			$columnString = implode(',', $columns);

			if ($entry->getType() == 'ova') {
				$this->select('DISTINCT ' . $columnString)
					->from(EntryRelation::TABLE, 'r')
					->leftJoin(EntryRelation::TABLE, 'r1', 'r.relation_id', '=', 'r1.relation_id')
					->where('r.entry_id', '=', $id);
			} else {
				$this->select('r.id, r.entry_id AS relation_id, r.relation_id AS entry_id, r.type')
					->from(EntryRelation::TABLE, 'r')
					->where('r.entry_id', '=', $id);
			}

			$entryRelations = $this->getResult();

			$entryRelations = array_filter($entryRelations, function ($entryRelation) use ($entry) {
				if ($entryRelation->getEntry() !== null && $entryRelation->getEntry() !== $entry) {
					return 1;
				}
				return 0;
			});
			if ($entry->getType() == 'ova') {
				/**  Entry @a @b */
				usort($entryRelations, function ($a, $b) {
					return $a->getEntry()->getTitle() <=> $b->getEntry()->getTitle();
				});
			}

			$relations = [];
			$entryRelations = array_map(function ($relation) use (&$relations) {
				$relationId = $relation->getEntry()->getTitle();
				if (! in_array($relationId, $relations)) {
					$relations[] = $relation->getEntry()->getTitle();
					return $relation;
				}
				return null;
			}, $entryRelations);

			return array_filter($entryRelations);
		}


		/**
		 * @param $entry
		 * @return array
		 */
		public function findByEntry($entry): array
		{
			$id = is_int($entry) ? $entry : $entry->getId();

			$result = $this->select('*')
				->from()
				->ob()
				->select('*')
				->from(EntryRelation::TABLE, 'r1')
				->where('r1.entry_id', '=', $id)
				->unionAll()
				->select('*')
				->from(EntryRelation::TABLE, 'r1')
				->where('r1.relation_id', '=', $id)
				->eb()
				->as('t1')
				->getResult();

			$ids = [];
			$entries = array_filter($result, function ($entry) use (&$ids) {
				if (! in_array($entry->getEntry()->getId(), $ids)) {
					$ids[] = $entry->getEntry()->getId();
					$ids[] = $entry->getRelation()->getId();
					return true;
				}
				return false;
			});

			return $entries;
		}

		/**
		 * @param $entry
		 * @param $relation
		 * @return array|int|mixed
		 */
		public function findRelationsByEntryAndRelation($entry, $relation)
		{
			$entry = is_numeric($entry) ? $entry : $entry->getId();
			$relation = is_numeric($relation) ? $relation : $relation->getId();
			return $this->addRaw('SELECT er3.* FROM (
				(SELECT * FROM entry_relations WHERE entry_id = "' . $entry . '" AND relation_id = "' . $relation . '")
				UNION ALL
				(SELECT * FROM entry_relations WHERE relation_id = "' . $entry . '" AND entry_id = "' . $relation . '")) er3')
				->getResult();
		}
	}