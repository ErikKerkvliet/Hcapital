<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 16-8-20
	 * Time: 14:23
	 */

	namespace v2\Database\Repository;


	use v2\Database\Entity\Thread;

	class ThreadRepository extends Repository
	{
		/**
		 * Entity class of repository
		 */
		protected $entity = Thread::class;

		/**
		 * @param array $limit
		 * @param string $author
		 * @return array|int|mixed
		 */
		public function findAllByPageTypeAndAuthor(array $limit, $type = null, $author = null)
		{
			$this->select()
				->from($this->entity::TABLE, 't');

			if ($type) {
				$this->where('t.type', '=', "'" . $type . "'");
			}

			if ($author) {
				if ($type) {
					$this->andWhere('t.author', '=', '"' . $author . '"');
				} else {
					$this->where('t.author', '=', '"' . $author . '"');
				}
			}

			return $this->orderBy('entry_id', 'ASC')
				->limit($limit[0] , $limit[1])
				->getResult();
		}

		public function insertThread(array $data)
		{
			$columns = array_keys($data);
			$this->addRaw('INSERT INTO ' . $this->entity::TABLE . ' (entry_id, number)
				SELECT 
					:nieuwe_entry_id,
					COALESCE(
						(SELECT MAX(number) + 1
						FROM ' . $this->entity::TABLE . '
						WHERE entry_id = :nieuwe_entry_id),
						0
					) AS nieuwe_number;');
		}
	}