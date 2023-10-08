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

		public function findRapidgatorLinksByEntry($entry, $multiple = false)
		{
			$entryId = $entry->getId();

			$minLink = $this->select('MIN(l.id) AS id')
				->from(Link::TABLE, 'l')
				->where('l.entry_id', '=', $entryId)
				->getResult()[0];

			$qb = $this->select()
				->from(Link::TABLE, 'l');
			if ($multiple) {
				$qb->where('l.id', '>=', $minLink->getId());
			} else {
				$qb->where('l.entry_id', '=', $entryId);
			}

			return $qb->andWhere('l.link', 'REGEXP', '"rapidgator.net"', '(')
				->orWhere('l.link', 'REGEXP', '"rg.to"', '', ')')
				->getResult();
		}

		public function findMexaShareLinksByEntry($entry, $multiple = false)
		{
			$entryId = $entry->getId();

			$minLink = $this->select('MIN(l.id) AS id')
				->from(Link::TABLE, 'l')
				->where('l.entry_id', '=', $entryId)
				->getResult()[0];

			$qb = $this->select()
				->from(Link::TABLE, 'l');
			if ($multiple) {
				$qb->where('l.id', '>=', $minLink->getId());
			} else {
				$qb->where('l.entry_id', '=', $entryId);
			}

			return $qb->andWhere('l.link', 'REGEXP', '"//mexa"')
				->getResult();
		}

		public function deleteByHost(int $entryId, string $host)
		{
			$query = 'DELETE FROM entry_links WHERE entry_id = ' . $entryId;
			$query .= ' AND link REGEXP "' . $host . '";';

			$this->runQuery(null, null, $query);
		}
	}