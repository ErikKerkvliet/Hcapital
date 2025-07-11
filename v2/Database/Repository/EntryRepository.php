<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 6-12-19
 * Time: 19:27
 */

namespace v2\Database\Repository;

use v2\Database\Entity\Character;
use v2\Database\Entity\Developer;
use v2\Database\Entity\Entry;
use v2\Database\Entity\EntryCharacter;
use v2\Database\Entity\EntryDeveloper;
use v2\Database\Entity\Link;
use v2\Database\EntityManager;

class EntryRepository extends Repository
{
	/**
	 * Entity class of repository
	 */
	protected $entity = Entry::class;

	/**
	 * @var array
	 */
	private $entryColumns = [];

	/**
	 * @var string
	 */
	private $entryColumnsString = '';

	public function __construct(EntityManager $em, $entityClass)
	{
		parent::__construct($em, $entityClass);

		$this->entryColumns = [
			'e.id', 'e.title', 'e.romanji',
			'e.released', 'e.size', 'e.website', 'e.information', 'e.password', 'e.type', 'e.time_type',
			'e.last_edited', 'e.created_at', 'e.downloads',
		];

		$this->entryColumnsString = implode(',', $this->entryColumns);
	}

	/**
	 * @param $search
	 * @param string $type
	 * @param array $orderBy
	 * @param array $limit
	 * @param string $timeType
	 * @return array|int|mixed
	 */
	public function findBySearch(
		$search,
		$type = '',
		array $orderBy = [],
		array $limit = [0, 25],
		$timeType = ''
	)
	{
		if ($search == null) {
			$this->select()
				->from(Entry::TABLE, 'e')
				->where('type', '=', "'" . $type . "'");

			if ($orderBy) {
				$this->orderBy($orderBy[0], $orderBy[1]);
			}
			
			return $this->limit($limit[0], $limit[1])
				->getResult();
		}
//			$search = $this->validateForQuery($search);

		$search = trim($search, ' ');

		if (strlen($search) < 3) {
			return [];
		}

		$this->select()
			->from(Entry::TABLE, 'e');

		$words = explode(' ', $search);

		$words = array_filter($words, function ($word) {
			if (!$word) {
				return false;
			}
			return true;
		});

		$wordCount = count($words);
//			$this->where('"' . $search . '"', 'REGEXP', 'title', '((');
//			$this->orWhere('"' . $search . '"', 'REGEXP', 'romanji', '', '))');

		foreach ($words as $key => $word) {
			if ($key === 0) {
				$eb = $wordCount == 1 ? ')' : '';
				$this->where('title', 'LIKE', "'%" . $word . "%'", '((', $eb);
				continue;
			}
			if ($key === $wordCount - 1) {
				$this->andWhere('title', 'LIKE', "'%" . $word . "%'", '', ')');
				continue;
			}
			$this->andWhere('title', 'LIKE', "'%" . $word . "%'");
		}

		$this->or();

		foreach ($words as $key => $word) {
			if ($key === 0) {
				$eb = $wordCount == 1 ? '))' : '';

				$this->whereSingle('romanji', 'LIKE', "'%" . $word . "%'", '(', $eb);
				continue;
			}
			if ($key === $wordCount - 1) {
				$this->andWhere('romanji', 'LIKE', "'%" . $word . "%'", '', '))');
				continue;
			}
			$this->andWhere('romanji', 'LIKE', "'%" . $word . "%'");
		}

		if ($type) {
			$this->andWhere('type', '=', "'" . $type . "'");
		}

		if ($timeType == 'upcoming') {
			$this->andWhere('time_type', 'IN', "('upc)'", '(');
		}
		if ($orderBy) {
			$this->orderBy($orderBy[0], $orderBy[1]);
		}
		if ($limit) {
			$this->limit($limit[0], $limit[1]);
		}
		$entries = $this->getResult();

		return is_int($entries) ?: array_map(function ($entry) {
			if (is_object($entry) && $entry->getTitle() == $entry->getRomanji()) {
				$entry->setRomanji('');
			}
			return $entry;
		}, $entries);
	}

	/**
	 * @param $char
	 * @param $type
	 * @param array $orderBy
	 * @param array $limit
	 * @return array|int|mixed
	 */
	public function findByChar(
		$char,
		$orderBy = [],
		$limit = []
	)
	{
		$condition = '=';
		if ($char == '35') {
			$condition = 'REGEXP';
			$char = "[^A-Za-z]";
		}

		$this->select()
			->from(Entry::TABLE, 'e')
			->where('LEFT(romanji, 1)', $condition, "'" . $char . "'")
			->andWhere('type', '=', "'game'")
			->orderBy($orderBy[0], $orderBy[1])
			->limit($limit[0], $limit[1]);

		return $this->getResult();
	}

	/**
	 * @param $character
	 * @param array $limit
	 * @param array $orderBy
	 * @return array|int|mixed
	 */
	public function findByCharacter(
		$character,
		$orderBy = [],
		$limit = []
	)
	{
		$id = is_int($character) ? $character : $character->getId();

		$this->select($this->entryColumnsString)
			->from(Entry::TABLE, 'e')
			->leftJoin(EntryCharacter::TABLE, 'ce', 'e.id', '=', 'ce.entry_id')
			->where('ce.character_id', '=', $id);

		if ($orderBy) {
			$this->orderBy($orderBy[0], $orderBy[1]);
		}
//			if ($limit) {
//				$this->limit($limit[0], $limit[1]);
//			}

		return $this->getResult();
	}

	/**
	 * @param $developer
	 * @param array $limit
	 * @param array $orderBy
	 * @return array|int|mixed
	 */
	public function findByDeveloper(
		$developer,
		$orderBy = [],
		$limit = []
	)
	{
		$id = is_int($developer) ? $developer : $developer->getId();

		$this->select($this->entryColumnsString)
			->from(EntryDeveloper::TABLE, 'ed')
			->leftJoin(Entry::TABLE, 'e', 'e.id', '=', 'ed.entry_id')
			->leftJoin(Developer::TABLE, 'd', 'd.id', '=', 'ed.developer_id')
			->where('d.id', '=', $id);

		if ($orderBy) {
			$this->orderBy($orderBy[0], $orderBy[1]);
		}

		return $this->getResult();
	}

	public function getUpcomingEntryCount()
	{
		$this->select('type')
			->from(Entry::TABLE, 'e')
			->where('time_type', '=', '"upc"');

		$result = $this->runQuery(null, null, $this->getSQL());

		$entries = ['game' => 0, 'ova' => 0, '3d' => 0, 'app' => 0];
		while ($row = mysqli_fetch_assoc($result)) {
			$entries[$row['type']]++;
		}
		return $entries;
	}

	/**
	 * @param $search
	 * @return array
	 */
	public function findAllTypes($search)
	{
		if (strlen($search) < 3) {
			return [];
		}

		$words = explode(' ', $search);

		$wordCount = count($words);

		$types = ['game', 'ova', '3d'];
		foreach ($types as $type) {
			$this->ob()
				->select('type')
				->from(Entry::TABLE, 'e');

			foreach ($words as $key => $word) {
				if ($key === 0) {
					$eb = $wordCount == 1 ? ')' : '';
					$this->where('title', 'LIKE', "'%" . $word . "%'", '((', $eb);
					continue;
				}
				if ($key === $wordCount - 1) {
					$this->andWhere('title', 'LIKE', "'%" . $word . "%'", '', ')');
					continue;
				}
				$this->andWhere('title', 'LIKE', "'%" . $word . "%'");
			}

			$this->or();
			foreach ($words as $key => $word) {
				if ($key === 0) {
					$eb = $wordCount == 1 ? '))' : '';

					$this->whereSingle('romanji', 'LIKE', "'%" . $word . "%'", '(', $eb);
					continue;
				}
				if ($key === $wordCount - 1) {
					$this->andWhere('romanji', 'LIKE', "'%" . $word . "%'", '', '))');
					continue;
				}
				$this->andWhere('romanji', 'LIKE', "'%" . $word . "%'");
			}
			$this->andWhere('type', '=', "'" . $type . "'")
				->limit(0, 1)
				->eb()
				->union();
		}
		$this->ob()
			->select('"developer" AS type')
			->from(Developer::TABLE, 'e');
		foreach ($words as $key => $word) {
			if ($key === 0) {
				$eb = $wordCount == 1 ? ')' : '';
				$this->where('kanji', 'LIKE', "'%" . $word . "%'", '((', $eb);
				continue;
			}
			if ($key === $wordCount - 1) {
				$this->andWhere('kanji', 'LIKE', "'%" . $word . "%'", '', ')');
				continue;
			}
			$this->andWhere('kanji', 'LIKE', "'%" . $word . "%'");
		}

		$this->or();

		foreach ($words as $key => $word) {
			if ($key === 0) {
				$eb = $wordCount == 1 ? '))' : '';

				$this->whereSingle('name', 'LIKE', "'%" . $word . "%'", '(', $eb);
				continue;
			}
			if ($key === $wordCount - 1) {
				$this->andWhere('name', 'LIKE', "'%" . $word . "%'", '', '))');
				continue;
			}
			$this->andWhere('name', 'LIKE', "'%" . $word . "%'");
		}

		$this->limit(0, 1)
			->eb()
			->union();

		$this->ob()
			->select('"character" AS type')
			->from(Character::TABLE, 'e');
		foreach ($words as $key => $word) {
			if ($key === 0) {
				$eb = $wordCount == 1 ? ')' : '';
				$this->where('romanji', 'LIKE', "'%" . $word . "%'", '((', $eb);
				continue;
			}
			if ($key === $wordCount - 1) {
				$this->andWhere('romanji', 'LIKE', "'%" . $word . "%'", '', ')');
				continue;
			}
			$this->andWhere('romanji', 'LIKE', "'%" . $word . "%'");
		}

		$this->or();

		foreach ($words as $key => $word) {
			if ($key === 0) {
				$eb = $wordCount == 1 ? '))' : '';

				$this->whereSingle('name', 'LIKE', "'%" . $word . "%'", '(', $eb);
				continue;
			}
			if ($key === $wordCount - 1) {
				$this->andWhere('name', 'LIKE', "'%" . $word . "%'", '', '))');
				continue;
			}
			$this->andWhere('name', 'LIKE', "'%" . $word . "%'");
		}
		$this->limit(0, 1)
			->eb();
		$types = [];
		$result = $this->runQuery(null, null, $this->getSQL());
		while ($row = mysqli_fetch_assoc($result)) {
			$types[] = $row['type'];
		}
		return $types;
	}

	public function findExportEntries($entries, $multiple = false)
	{
		$qb = $this->select()
			->from(Entry::TABLE, 'e');

		if ($multiple) {
			$id = $entries[0];
			$qb->where('e.id', '>=', $id);
		} else {
			$ids = implode(',', $entries);
			$qb->where('e.id', 'IN (', $ids . ')');
		}

		return $qb->getResult();
	}

	public function findUpcomingEntries($type, $orderBy, $limit)
	{
		$ob = $type == 'ova' ? '(' : '';
		$this->select()
			->from(Entry::TABLE, 'e')
			->where('type', '=', '"' . $type . '"', $ob);

		if ($type == 'ova') {
			$this->orWhere('type', '=', '"app"', '', ')');
		}
		$this->andWhere('time_type', '=', '"upc"')
			->orderBy($orderBy[0], $orderBy[1])
			->limit($limit[0], $limit[1]);

		return $this->getResult();
	}

	public function findEntriesForAdding($type)
	{
		$sql = sprintf("SELECT * FROM (SELECT id, romanji FROM entries WHERE type = '%s' AND romanji != ''
				UNION ALL 
				SELECT id, title AS romanji FROM entries WHERE type = '%s' AND romanji = '') AS t1
				GROUP BY romanji ORDER BY romanji ASC",
			$type,
			$type);

		$this->addRaw($sql);

		return $this->getResult();
	}

	public function findLatestEntries($type, $limit)
	{
		$qb = $this->select()
			->from(Entry::TABLE, 'e');

		if ($type == 'ova') {
			$qb->where('type', 'IN', '("ova", "3d")');
		} else {
			$qb->where('type', '=', '"' . $type . '"');
		}

		$qb->andWhere('time_type', '=', '"new"')
			->andWhere('created_at', '>', '"2019-09-01 00:00:00"')
			->orderBy('last_edited', 'DESC')
			->limit($limit[0], $limit[1]);

		return $qb->getResult();
	}

	public function findRandomEntries($quantity, $types)
	{
		return $this->select()
			->from(Entry::TABLE, 'e')
			->where('e.type', "IN", "('" . implode("','", $types) . "')")
			->andWhere('e.time_type', 'NOT IN (', "'inv', 'upc')")
			->orderBy('RAND()', 'asc')
			->limit($quantity)
			->getResult();
	}

	public function findNotInsertedByType($type = 'ova')
	{
		return $this->select()
			->from(Entry::TABLE, 'e')
			->where('e.id', ' NOT IN (', 'SELECT entry_id FROM threads)')
			->andWhere('e.type', '=', '"' . $type . '"')
			->getResult();
	}

	public function findByLastEdited($lastEdited)
	{
		return $this->select('e.id')
			->from(Entry::TABLE, 'e')
			->where('e.last_edited', '>=', "'" . $lastEdited . "'")
			->getResult();
	}

	public function updateId(int $id, int $newId) 
	{
		$query = 'UPDATE ' . Entry::TABLE . ' SET id = ' . $newId . ' WHERE id = ' . $id;
		$this->runQuery(null, null, $query);
	}

	public function findLastAddedByType($type, $page) 
	{
		$joinSql = "(SELECT entry_id, MAX(created_at) as max_created FROM entry_links GROUP BY entry_id ORDER BY max_created DESC LIMIT " . $page * 5 . ", 5) latest_links ON e.id = latest_links.entry_id";
		return $this->select($this->entryColumnsString)
			->from(Entry::TABLE, 'e')
			->innerJoinQuery($joinSql)
			->where('e.type', '=', '"' . $type . '"')
			->andWhere('e.time_type', '=', '"old"')
			->orderBy('latest_links.max_created desc, e.last_edited', 'DESC')
			->getResult();
	}
}