<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 7-12-19
	 * Time: 1:07
	 */

	namespace v2\Database\Repository;

	use HostResolver;
	use v2\Database\Entity\Entry;
	use v2\Database\Entity\Host;
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
					foreach (Host::HOSTS as $host) {
						if (strpos($row['link'], $host) !== false) {
							$data[] = $host . '|!|' . $row['link'];
						}
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

		public function findByEntryAndHost($entry, $host, $multiple = false)
		{
			$this->findMinMax($entry, $multiple);

			$conditionCount = count(HostResolver::REGEXP_PATTERNS[$host]);
			foreach (HostResolver::REGEXP_PATTERNS[$host] as $key => $pattern) {
				if ($conditionCount > 1) {
					if (! $key) {
						$this->andWhere('l.link', 'REGEXP', '"' . $pattern . '"', '(');
					} else if ($key == $conditionCount - 1) {
						$this->orWhere(
							'l.link',
							'REGEXP',
							'"' . $pattern . '"',
							'',
							')'
						);
					} else {
						$this->orWhere('l.link', 'REGEXP', '"' . $pattern . '"');
					}
				} else {
					$this->andWhere('l.link', 'REGEXP', '"' . $pattern . '"');
				}
			}
			$this->limit(0, 20);

			return $this->getResult();
		}

		public function findByEntryAndHostAndPart($entry, $host, $part = 0)
		{
			$this->select()
				->from(Link::TABLE, 'l')
				->where('l.entry_id', '=', $entry->getId())
				->andWhere('l.link', 'REGEXP', '"' . $host . '"');

			if ($part) {
				$this->andWhere('l.part', '=', $part);
			}

			return $this->getResult();
		}


		public function findLinksFromLast5Hours($host, $entry = null)
		{
			$this->select('l.*')
				->from(Link::TABLE, 'l')
				->leftJoin(Entry::TABLE, 'e', 'e.id', '=', 'l.entry_id');

			// Bereken de tijd van 5 uur geleden
			$dayAgo = date('Y-m-d H:i:s', strtotime('-1 hours'));

			// Voeg een WHERE-clausule toe om alleen links van het laatste uur op te halen
			$this->where('l.created_at', '>=', '"' . $dayAgo . '"');
			$this->andWhere('l.link', 'REGEXP', '"' . $host . '"');
			if ($entry) {
				$this->andWhere('l.entry_id', '>=', $entry->getId());
			}

			$this->limit(0, 100);

			return $this->runQuery(Link::class, null, $this->getSQL());
		}
		
		public function deleteByHost($entryId, string $host, $parts = [])
		{
			$query = 'DELETE FROM entry_links WHERE entry_id = ' . $entryId;
			$query .= ' AND link REGEXP "' . $host . '"';
			if ($parts) {
				$query .= ' AND part IN (' . implode(',', $parts) . ')';
			}
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