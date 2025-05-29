<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 27-12-19
	 * Time: 21:51
	 */

	namespace v2\Database\Repository;

	use v2\Database\Entity\Character;
	use v2\Database\Entity\EntryCharacter;
	use v2\Database\Entity\Entry;
	use v2\Database\EntityManager;

	class EntryCharacterRepository extends Repository
	{
		/**
		 * Entity class of repository
		 */
		protected $entity = EntryCharacter::class;

		/**
		 * @var array
		 */
		private $characterColumns = [];

		/**
		 * @var array
		 */
		private $entryColumns = [];

		/**
		 * @var string
		 */
		private $characterColumnsString = '';

		/**
		 * @var string
		 */
		private $entryColumnsString = '';

		/**
		 * EntryCharacterRepository constructor.
		 * @param EntityManager $em
		 * @param $entityClass
		 */
		public function __construct(EntityManager $em, $entityClass)
		{
			parent::__construct($em, $entityClass);

			$this->characterColumns = [
				'c.id', 'c.name', 'c.romanji', 'c.age', 'c.gender', 'c.height',
				'c.weight', 'c.cup', 'c.bust', 'c.waist', 'c.hips',
			];

			$this->characterColumnsString = implode(',', $this->characterColumns);

			$this->entryColumns = [
				'e.id', 'e.title', 'e.romanji', 'e.released', 'e.size',
                'e.website', 'e.information', 'e.password', 'e.type', 'e.time_type',
				'e.last_edited', 'e.created_at', 'e.downloads',
			];

			$this->entryColumnsString = implode(',', $this->entryColumns);

		}

		/**
		 * @param Entry $entry
		 * @param array $columns
		 * @return null|Character[]
		 */
		public function findCharactersByEntry($entry, $columns = [])
		{
			$id = is_int($entry) ? $entry : $entry->getId();

			if (! $columns) {
				$columns = $this->characterColumns;
			} else {
				foreach ($columns as &$column) {
					$column = substr($column, 0, 2) == 'c.' ?: 'c.' . $column;
				}
			}

			return $this->select($this->characterColumnsString)
				->from(Character::TABLE, 'c')
				->leftJoin(EntryCharacter::TABLE, 'ce', 'c.id', '=', 'ce.character_id')
				->where('ce.entry_id', '=', $id)
				->getResult(Character::class);
		}

		/**
		 * @param Character $character
		 * @param array $columns
		 * @return array
		 */
		public function findEntryByCharacters(Character $character, array $columns = []): array
		{
			$id = $character->getId();

			if (! $columns) {
				$columns = $this->entryColumns;
			} else {
				foreach ($columns as &$column) {
					$column = substr($column, 0, 2) == 'e.' ?: 'e.' . $column;
				}
			}

			return $this->select($this->entryColumnsString)
				->from(Entry::TABLE, 'e')
				->leftJoin(EntryCharacter::TABLE, 'ce', 'e.id', '=', 'ce.entry_id')
				->where('ce.character_id', '=', $id)
				->getResult(Entry::class);
		}
	}