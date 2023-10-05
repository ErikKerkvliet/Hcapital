<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 1-12-19
	 * Time: 1:20
	 */

	namespace v2\Database\Repository;


	use v2\Classes\AdminCheck;
	use v2\Database\Entity\Developer;
	use v2\Database\EntityManager;
	use v2\Database\QueryBuilder;
	use v2\Database\QueryHandler;

	use v2\Database\Repository\BannedRepository;
	use v2\Database\Repository\BrokenRepository;
	use v2\Database\Repository\CharacterRepository;
	use v2\Database\Repository\EntryCharacterRepository;
	use v2\Database\Repository\DeveloperRepository;
	use v2\Database\Repository\DeveloperRelationRepository;
	use v2\Database\Repository\DownloadRepository;
	use v2\Database\Repository\EntryRepository;
	use v2\Database\Repository\EntryRelationRepository;
	use v2\Database\Repository\LinkRepository;
	use v2\Database\Repository\ToDoRepository;

	class Repository extends QueryBuilder
	{
		/**
		 * @var EntityManager|null
		 */
		protected $em = null;

		/**
		 * @var null
		 */
		private $entityClass = null;

		/**
		 * Repository constructor.
		 * @param EntityManager $em
		 * @param $entityClass
		 */
		public function __construct(EntityManager $em, $entityClass)
		{
			$this->entityClass = $entityClass;

			$this->em = $em;
		}

		/**
		 * @param $entityClass
		 * @param $id
		 * @return mixed
		 */
		public function find($entityClass, $entry)
		{
			$id = is_numeric($entry) ? $entry : $entry->getId();

			$this->entity = $entityClass;

			$this->includeEntity();

			$this->query = "SELECT * FROM " . $this->entity::TABLE . " WHERE id = " . $id;

			$result = $this->runQuery($this->entity);

			return $result ? $result[0] : [];
		}

		/**
		 * @param $entityClass
		 * @param array $conditions
		 * @param array $operators
		 * @param int $limit
		 * @return array
		 */
		public function findBy(array $conditions, array $orderBy = [], array $limit = [])
		{
			$this->query = "SELECT * FROM " . $this->entityClass::TABLE . " WHERE";
			$comma = '';
			foreach($conditions as $originalKey => $condition) {
				$comma = ! $comma ? ' ' : ' AND ';
				$key = $this->em->reverseMapFunctionName($originalKey);
				$condition = is_object($condition) ? $condition->getId() : $condition;

				$this->query .= $comma . $key . "='" . $condition . "'";
			}

			$comma = '';
			foreach ($orderBy as $by => $order) {
				$comma = ! $comma ? ' ' : ' ';

				$this->query .= $comma . 'ORDER BY ' . $by . " " . $order . "";
			}

			if ($limit) {
				$this->query .= ' LIMIT ' . implode(', ', $limit);
			}
			return $this->runQuery($this->entityClass);
		}

		public function findById(array $ids = [], $orderBy = [], $limit = [])
		{
			$ids = is_array($ids) ? implode(',', $ids) : $ids;

			$this->query = "SELECT * FROM " . $this->entityClass::TABLE . " WHERE ";
			$this->query .= "id IN (" . $ids . ") ";

			$comma = '';
			foreach ($orderBy as $by => $order) {
				$comma = ! $comma ? ' ' : ' ';

				$this->query .= $comma . 'ORDER BY ' . $by . " " . $order . "";
			}

			if ($limit) {
				$this->query .= ' LIMIT ' . implode(', ', $limit);
			}

			return $this->runQuery($this->entityClass);
		}

		/**
		 * @param $entityClass
		 * @param array $conditions
		 * @param array $operators
		 * @return mixed
		 */
		public function findOneBy(array $conditions, array $findBy = [])
		{
			$result = $this->findBy($conditions, $findBy, [1]);
			return count($result) ? $result[0] : null;
		}

		/**
		 * @param $entityClass
		 * @return array
		 */
		public function findAll($orderBy = [], $limit = [])
		{
			$this->select();
			$this->from($this->entity::TABLE, 't');

			if ($orderBy && count($orderBy) == 2) {
				$this->orderBy($orderBy[0], $orderBy[1]);
			}

			if ($limit && count($limit) == 2) {
				$this->limit($limit[0], $limit[1]);
			}
			return $this->getResult();
		}

		/**
		 * @param $entityClass
		 */
		private function includeEntity()
		{
			$exploded = explode('\\', $this->entity);
			$entity = end($exploded);

			$fileName = $GLOBALS['source'] == 'ajax' ? 'Database/Entity/' . $entity . '.php' :
				'v2/Database/Entity/' . $entity . '.php';

			require_once($fileName);
		}

	}
?>
