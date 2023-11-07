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
					} else if (strpos($row['link'], 'katfile') !== false) {
						$data[] = 'katfile|!|' . $row['link'];
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

		public function findRapidgatorLinksByEntry($entry, $multiple = false)
		{
			$this->findMinMax($entry, $multiple);

			return $this->andWhere('l.link', 'REGEXP', '"rapidgator.net"', '(')
				->orWhere('l.link', 'REGEXP', '"rg.to"', '', ')')
				->getResult();
		}

		public function findMexashareLinksByEntry($entry, $multiple = false)
		{
			$this->findMinMax($entry, $multiple);

			return $this->andWhere('l.link', 'REGEXP', '"//mexa"', '(')
				->orWhere('l.link', 'REGEXP', '"www.mexa"')
				->orWhere('l.link', 'REGEXP', '"sh.net"', '', ')')
				->getResult();
		}

		public function findKatfileLinksByEntry($entry, $multiple = false)
		{
			$this->findMinMax($entry, $multiple);

			return $this->andWhere('l.link', 'REGEXP', '"//katfile.com"')
				->getResult();
		}

		public function deleteByHost(int $entryId, string $host)
		{
			$query = 'DELETE FROM entry_links WHERE entry_id = ' . $entryId;
			$query .= ' AND link REGEXP "' . $host . '";';

			$this->runQuery(null, null, $query);
		}

		public function findBetweenEntry($start, $end)
		{
			$start = is_int($start) ? $start : $start->getId();
			$end = is_int($end) ? $end : $end->getId();

			return $this->select()
				->from(Link::TABLE, 'l')
				->where('l.entry_id', '>=', $start)
				->andWhere('l.entry_id', '<=', $end)
				->orderBy('l.entry_id', 'ASC')
				->getResult();
		}

		private function findMinMax($entry, $multiple) {
			$entryId = $entry->getId();

			$minLink = $this->select('MIN(l.id) AS id')
				->from(Link::TABLE, 'l')
				->where('l.entry_id', '=', $entryId)
				->getResult()[0];

			$maxLink = $this->select('MAX(l.id) AS id')
				->from(Link::TABLE, 'l')
				->getResult()[0];

			$this->select()
				->from(Link::TABLE, 'l');

			if ($multiple) {
				$this->where('l.id', '>=', $minLink->getId())
					->andWhere('l.id', '>', ($maxLink->getId() - 30));
			} else {
				$this->where('l.entry_id', '=', $entryId);
			}
		}
	}