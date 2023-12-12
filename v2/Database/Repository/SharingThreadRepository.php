<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 19-8-20
	 * Time: 23:36
	 */

	namespace v2\Database\Repository;


	use v2\Database\Entity\SharingThread;
	use v2\Database\Entity\Thread;

	class SharingThreadRepository extends Repository
	{
		/**
		 * Entity class of repository
		 */
		protected $entity = SharingThread::class;

		/**
		 * @param $title
		 * @param null $author
		 * @return SharingThread[]
		 */
		public function findByTitleAsPartialTitleAuthorAndType($title, $author = 'yuuichi_sagara', $type = null): array
		{
			$this->select()
				->from($this->entity::TABLE, 'st')
				->regexp('st.title', $title);

			if ($author) {
				$this->andWhere('author', '=', '"' . $author . '"');
			}

			if ($type) {
				$this->andWhere('type', '=', '"' . $type . '"');
			}

			return $this->getResult();
		}

		/**
		 * @param string $type
		 * @param null $author
		 * @return array
		 */
		public function findUnmatchedByTypeAndAuthor($type = 'ova', $author = null): array
		{
			$this->select('st.id, st.title, st.author, st.type, st.url')
				->from(SharingThread::TABLE, 'st')
				->leftJoin(Thread::TABLE, 't', 'st.url', '=', 't.url')
				->where('st.type', 'IS NULL', '')->dq();

			if ($author) {
				$this->andWhere('st.author', '=', '"' . $author . '"');
			}

			$result = $this->getResult(false);

			$data = [];
			while ($row = mysqli_fetch_assoc($result)) {
				$data[] = [
					'id'        => $row['id'],
					'title'     => $row['title'],
					'author'    => $row['author'],
					'type'      => $row['type'],
					'url'       => $row['url'],
					];
			}
			return $data;
		}
	}
