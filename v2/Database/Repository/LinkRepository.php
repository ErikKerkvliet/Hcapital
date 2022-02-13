<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 7-12-19
	 * Time: 1:07
	 */

	namespace v2\Database\Repository;

	use v2\Database\Entity\Entry;
	use v2\Database\Entity\Link;
	use v2\Database\Entity\Thread;

	class LinkRepository extends Repository
	{
		/**
		 * Entity class of repository
		 */
		protected $entity = Link::class;

		public function findByEntryAndNoComment($entry)
		{
			$id = is_int($entry) ? $entry : $entry->getId();

			return $this->select()
				->from(Link::TABLE, 'el')
				->where('`comment`', '!=', '""')
				->andWhere('el.entry_id', '=', $id)
				->getResult();
		}

		public function getEntryLinks($id, $author)
		{
			$this->select()
				->from(Link::TABLE, 'l')
				->leftJoin(Entry::TABLE, 'e', 'e.id', '=', 'l.entry_id');

			if ($author) {
				$this->leftJoin(Thread::TABLE, 't', 't.entry_id', '=', 'e.id');
			}

			$this->where('l.entry_id', '=', $id)
				->andWhere('e.type', '=', '"ova"');

			if ($author) {
				$this->andWhere('t.author', '=', '"' . $author . '"');
			}

			$this->groupBy('l.link');

			$result = $this->getResult(false);

			$data = [];
			while ($row = mysqli_fetch_assoc($result)) {
				if (! $row['comment']) {
					if (strpos($row['link'], 'rapidgator') !== false) {
						$data[] = 'rapidgator|!|' . $row['link'];
					} else {
						$data[] = 'mexashare|!|' . $row['link'];
					}
					continue;
				}
				$data[] = $row['comment'] . '|!|' . $row['link'];
			}

			return implode(',|,', $data);
		}

		public function findByLinkPart(string $text)
		{
			return $this->select()
				->from(Link::TABLE, 'l')
				->where('l.link', 'REGEXP', '"' . $text . '"')
				->getResult();
		}
	}