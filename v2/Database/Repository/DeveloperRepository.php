<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 1-12-19
	 * Time: 14:47
	 */

	namespace v2\Database\Repository;

	use v2\Database\Entity\Developer;
	use v2\Database\Entity\DeveloperRelation;
	use v2\Database\Entity\EntryDeveloper;
	use v2\Database\EntityManager;

	class DeveloperRepository extends Repository
	{
		/**
		 * Entity class of repository
		 */
		protected $entity = Developer::class;

		/**
		 * @var array
		 */
		private $developerColumns = [];

		/**
		 * @var string
		 */
		private $developerColumnsString = '';

		public function __construct(EntityManager $em, $entityClass)
		{
			parent::__construct($em, $entityClass);

			$this->developerColumns = [
				'd.id', 'd.name', 'd.kanji', 'd.type', 'd.homepage',
			];

			$this->developerColumnsString = implode(',', $this->developerColumns);
		}


		public function findBySearch(
			$search,
			array $orderBy = ['name', 'ASC'],
			$limit = []
		)
		{
			if (! $search) {
				 return $this->select()
					->from(Developer::TABLE, 'd')
					->orderBy($orderBy[0], $orderBy[1])
					->limit($limit[0], $limit[1])
					->getResult();
			}

//			$search = $this->validateForQuery($search);

			$search = trim($search, ' ');

			if (strlen($search) < 3) {
				return [];
			}

			$this->select()
				->from(Developer::TABLE, 'd');

			$words = explode(' ', $search);

			$wordCount = count($words);
//			$this->where('"' . $search . '"', 'REGEXP', 'name', '(');
//			$this->orWhere('"' . $search . '"', 'REGEXP', 'kanji', '', ')');

			foreach ($words as $key => $word) {
				if ($key === 0) {
					$eb = $wordCount == 1 ? ')' : '';
					$this->where('name', 'LIKE', "'%" . $word . "%'", '((', $eb);
					continue;
				}
				if ($key === $wordCount - 1) {
					$this->andWhere('name', 'LIKE', "'%" . $word . "%'", '', ')');
					continue;
				}
				$this->andWhere('name', 'LIKE', "'%" . $word . "%'");
			}

			$this->or();

			foreach ($words as $key => $word) {
				if ($key == 0) {
					$eb = $wordCount == 1 ? '))' : '';

					$this->whereSingle('kanji', 'LIKE', "'%" . $word . "%'", '(', $eb);
					continue;
				}
				if ($key === $wordCount - 1) {
					$this->andWhere('kanji', 'LIKE', "'%" . $word . "%'", '', '))');
					continue;
				}
				$this->andWhere('kanji', 'LIKE', "'%" . $word . "%'");
			}

			if ($orderBy) {
				$this->orderBy($orderBy[0], $orderBy[1]);
			}
			if ($limit) {
				$this->limit($limit[0], $limit[1]);
			}
			return $this->getResult();
		}

		public function findByChar($char, $orderBy, $limit = [])
		{
			$condition = '=';
			if ($char == '35') {
				$condition = 'REGEXP';
				$char = "[^A-Za-z]";
			}

			$this->select()
				->from(Developer::TABLE, 'd')
				->where('LEFT(name, 1)', $condition, "'" . $char . "'")
				->orderBy($orderBy[0], $orderBy[1])
				->limit($limit[0], $limit[1]);

			return $this->getResult();
		}

		public function findByRelation($developer)
		{
			$id = is_int($developer) ? $developer : $developer->getId();

			 return $this->select('d.id, d.name, d.kanji, d.homepage, d.type')
				->from(Developer::TABLE, 'd')
				->leftJoin(DeveloperRelation::TABLE, 'dr', 'd.id', '=', 'dr.developer_id')
				->or()
				->addRaw('d.id = dr.relation_id')
				->where('dr.relation_id', '=', $id, '(')
				->orWhere('dr.developer_id', '=', $id, '', ')')
				->andWhere('d.id', '!=', $id)
				->getResult();
		}

		public function findByType($type, $orderBy = [], $limit = [], $char = false)
		{
			$this->select()
				->from(Developer::TABLE, 'd')
				->where('d.type', '=', "'" . $type . "'");

			if ($char && $char != 'all') {
				$condition = $char == '35' ? 'REGEXP' : '=';
				$char = $char == '35' ? "[^A-Za-z]" : $char;

				$this->andWhere('LEFT(name, 1)', $condition, "'" . $char . "'");
			}
			if ($orderBy) {
				$this->orderBy($orderBy[0], $orderBy[1]);
			}
			if ($limit) {
				$this->limit($limit[0], $limit[1]);
			}
			return $this->getResult();
		}

		public function findByEntry($entry)
		{
			$id = is_int($entry) ? $entry : $entry->getId();

			return $this->select($this->developerColumnsString)
				->from(Developer::TABLE, 'd')
				->leftJoin(EntryDeveloper::TABLE, 'ed', 'ed.developer_id', '=', 'd.id')
				->where('ed.entry_id', '=', $id)
				->getResult();
		}
	}
?>