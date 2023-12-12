<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 18-1-20
	 * Time: 17:26
	 */

	namespace v2\Database\Repository;

	use v2\Classes\AdminCheck;
	use v2\Database\Entity\Entry;
	use v2\Database\Entity\EntryRelation;
	use v2\Database\Entity\SeriesRelation;

	class SeriesRelationRepository extends Repository
	{
		/**
		 * Entity class of repository
		 */
		protected $entity = SeriesRelation::class;

		/**
		 * @param $entry
		 * @return SeriesRelation[]
		 */
		public function findAllFromSeries($entry)
		{
			$id = is_int($entry) ? $entry : $entry->getId();

			return $this->select('e.id, r.entry_id, r.relation_id, e.title, e.romanji, e.released')
				->from(Entry::TABLE, 'e')
				->leftJoin(EntryRelation::TABLE, 'r', 'e.id', '=', 'r.entry_id')
				->where('r.relation_id', '=', $id)
				->getResult();
		}

		/**
		 * @param $search
		 * @param string $type
		 * @param array $orderBy
		 * @param array $limit
		 * @param string $timeType
		 * @return array
		 */
		public function findBySearch(
			$search,
			$type = '',
			array $orderBy = ['title', 'ASC'],
			array $limit = [0, 25]
		)
		{
			if (AdminCheck::checkForLocal()) {
				$search = trim(explode('-', $search)[0]);
			}
			if ($search == null) {
				if (! $limit) {
					$limit = [0, 25];
				}
				if (! $orderBy) {
					$orderBy = ['released', 'ASC'];
				}
				$this->addRaw('SELECT DISTINCT IFNULL(e.id, t2.relation_id) id, t2.relation_id, IFNULL(e.title, t2.title) title, IFNULL(e.romanji, t2.romanji) romanji, IFNULL(e.released, t2.released) released FROM
					(SELECT r.entry_id, t1.relation_id, t1.released, t1.title, t1.romanji FROM (SELECT IFNULL(r.relation_id, e.id) relation_id, e.id, e.released, e.title, e.romanji FROM entries e 
					LEFT JOIN entry_relations r ON r.entry_id = e.id WHERE e.type = "' . $type . '"
					GROUP BY IFNULL(r.relation_id, e.id)
					ORDER BY e.' . $orderBy[0] . ' ' . $orderBy[1] . ' LIMIT ' . $limit[0] . ', ' . $limit[1] . ') t1
					LEFT JOIN entry_relations r ON t1.relation_id = r.relation_id) t2
					LEFT JOIN entries e ON e.id = t2.entry_id
					ORDER BY t2.' . $orderBy[0] . ' ' . $orderBy[1]);

				return $this->getResult();
			}

			$search = trim($search, ' ');

			if (strlen($search) < 3) {
				return [];
			}
//			$search = $this->validateForQuery($search);

			$words = explode(' ', $search);

			$wordCount = count($words);

			foreach ($words as $key => $word) {
				if ($key === 0) {
					$eb = $wordCount == 1 ? ')' : '';
					$this->where('e.title', 'LIKE', "'%" . $word . "%'", '((', $eb);
					continue;
				}
				if ($key === $wordCount - 1) {
					$this->andWhere('e.title', 'LIKE', "'%" . $word . "%'", '', ')');
					continue;
				}
				$this->andWhere('e.title', 'LIKE', "'%" . $word . "%'");
			}

			$this->or();

			foreach ($words as $key => $word) {
				if ($key === 0) {
					$eb = $wordCount == 1 ? '))' : '';

					$this->whereSingle('e.romanji', 'LIKE', "'%" . $word . "%'", '(', $eb);
					continue;
				}
				if ($key === $wordCount - 1) {
					$this->andWhere('e.romanji', 'LIKE', "'%" . $word . "%'", '', '))');
					continue;
				}
				$this->andWhere('e.romanji', 'LIKE', "'%" . $word . "%'");
			}
			$this->andWhere('e.type', '=', "'" . $type . "'");
//
//			if ($timeType == 'upcoming') {
//				$this->andWhere('e.time_type', 'IN', "('app', 'upc)'");
//			}
			$where = $this->getSQL();
			$this->clear();

			$this->addRaw('SELECT IFNULL(e.id, t2.relation_id) id, t2.relation_id, IFNULL(e.title, t2.title) title, IFNULL(e.romanji, t2.romanji) romanji, IFNULL(e.released, t2.released) released FROM
					(SELECT r.entry_id, t1.relation_id, t1.released, t1.title, t1.romanji FROM (SELECT IFNULL(r.relation_id, e.id) relation_id, e.id, e.released, e.title, e.romanji FROM entries e 
					LEFT JOIN entry_relations r ON r.entry_id = e.id ' . $where . '
					GROUP BY IFNULL(r.relation_id, e.id)
					ORDER BY e.' . $orderBy[0] . ' ' . $orderBy[1] . ' LIMIT ' . $limit[0] . ', ' . $limit[1] . ') t1
					LEFT JOIN entry_relations r ON t1.relation_id = r.relation_id) t2
					LEFT JOIN entries e ON e.id = t2.entry_id ORDER BY t2.' . $orderBy[0] . ' ' . $orderBy[1]);

			return $this->getResult();
		}

		/**
		 * @param $char
		 * @param string $type
		 * @param array $orderBy
		 * @param array $limit
		 * @param string $timeType
		 * @return array|int|mixed
		 */
		public function findByChar(
			$char,
			array $orderBy = [],
			array $limit = [],
			string $type = ''
		)
		{
			if ($char == 'all') {
				header('Location: /?v=2&l=' . substr($type, 0, 1));
			}
			$condition = '=';
			if ($char == '35') {
				$condition = 'REGEXP';
				$char = "[^A-Za-z]";
			}

			if (! $limit) {
				$limit = [0, 25];
			}
			if (! $orderBy) {
				$orderBy = ['released', 'ASC'];
			}
			$this->addRaw('SELECT IFNULL(e.id, t2.relation_id) id, t2.relation_id, IFNULL(e.title, t2.title) title, IFNULL(e.romanji, t2.romanji) romanji, IFNULL(e.released, t2.released) released FROM
					(SELECT r.entry_id, t1.relation_id, t1.released, t1.title, t1.romanji FROM (SELECT IFNULL(r.relation_id, e.id) relation_id, e.id, e.released, e.title, e.romanji FROM entries e 
					LEFT JOIN entry_relations r ON r.entry_id = e.id WHERE e.type = "' . $type . '" AND LEFT(e.romanji, 1) ' . $condition . ' "' . $char . '"
					GROUP BY IFNULL(r.relation_id, e.id)
					ORDER BY ' . $orderBy[0] . ' ' . $orderBy[1] . ' LIMIT ' . $limit[0] . ', ' . $limit[1] . ') t1
					LEFT JOIN entry_relations r ON t1.relation_id = r.relation_id) t2
					LEFT JOIN entries e ON e.id = t2.entry_id
					ORDER BY ' . $orderBy[0] . ' ' . $orderBy[1]);

			return $this->getResult();
		}


		public function findByDeveloper(
			$developer,
			array $orderBy = [],
			array $limit = []
		) {
			$id = is_int($developer) ?: $developer->getId();
			$type = is_int($developer) ? 'ova' : $developer->getType();

			if ($orderBy) {
					$orderBy[0] = 't3.' . $orderBy[0];
			}

			$this->addRaw('SELECT * FROM (SELECT IFNULL(e.id, t2.relation_id) id, t2.relation_id, IFNULL(e.title, t2.title) title, IFNULL(e.romanji, t2.romanji) romanji, IFNULL(e.released, t2.released) released FROM
			   (SELECT r.entry_id, t1.relation_id, t1.released, t1.title, t1.romanji FROM (SELECT IFNULL(r.relation_id, e.id) relation_id, e.id, e.released, e.title, e.romanji FROM entries e
			   LEFT JOIN entry_relations r ON r.entry_id = e.id
			   LEFT JOIN entry_developers ed ON ed.entry_id = e.id
			   LEFT JOIN developers d ON d.id = ed.developer_id
			
			   WHERE e.type = "' . $type . '" AND d.id = ' . $id . ' GROUP BY IFNULL(r.relation_id, e.id) ORDER BY e.released DESC ) t1
			
			   LEFT JOIN entry_relations r ON t1.relation_id = r.relation_id) t2
			   LEFT JOIN entries e ON e.id = t2.entry_id ORDER BY t2.released DESC) t3 GROUP BY t3.title ORDER BY ' . $orderBy[0] . ' ' . $orderBy[1]);

			return $this->getResult();
		}
	}