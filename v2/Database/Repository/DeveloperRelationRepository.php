<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 27-12-19
	 * Time: 21:52
	 */

	namespace v2\Database\Repository;


	use v2\Database\Entity\Developer;
	use v2\Database\Entity\DeveloperRelation;
	use v2\Database\EntityManager;

	class DeveloperRelationRepository extends Repository
	{
		/**
		 * Entity class of repository
		 */
		protected $entity = DeveloperRelation::class;

		/**
		 * @var array
		 */
		private $developerColumns = [];

		/**
		 * EntryCharacterRepository constructor.
		 * @param EntityManager $em
		 * @param $entityClass
		 */
		public function __construct(EntityManager $em, $entityClass)
		{
			parent::__construct($em, $entityClass);

			$this->developerColumns = [
				'd.id', 'd.developer', 'd.kanji', 'd.type', 'd.homepage', 'd.logo',
			];
		}

		/**
		 * @param Developer $developer
		 * @param array $columns
		 * @return array
		 */
		public function findDeveloperByRelation($developer, $columns = [])
		{
			$id = is_int($developer) ? $developer : $developer->getId();

			if (! $columns) {
				$columns = $this->developerColumns;
			} else {
				foreach ($columns as &$column) {
					$column = substr($column, 0, 2) == 'd.' ?: 'd.' . $column;
				}
			}
			$columnString = implode(',', $columns);

			$developerArray1 = $this->findDeveloper($id, $columnString, [1, 2]);
			$developerArray2 = $this->findDeveloper($id, $columnString, [2, 1]);

			$merged = array_merge($developerArray1, $developerArray2);

			$developers = [];
			foreach($merged as $developer) {
				if (! in_array($developer, $developers)) {
					$developers[] = $developer;
				}
			}

			return $developers;
		}

		/**
		 * @param $id
		 * @param $columnString
		 * @param $developers
		 * @return array
		 */
		private function findDeveloper($id, $columnString, $developers)
		{
			return $this->select($columnString)
				->from(Developer::TABLE, 'd')
				->leftJoin(DeveloperRelation::TABLE, 'dr', 'd.id', '=', 'dr.developer_' . $developers[0] . '_id')
				->where('dr.developer_' . $developers[1] . '_id', '=', $id)
				->getResult();
		}
	}