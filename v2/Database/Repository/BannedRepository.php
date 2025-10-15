<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 1-12-19
	 * Time: 14:47
	 */

	namespace v2\Database\Repository;

	use v2\Database\Entity\Banned;
	use v2\Database\Entity\Download;
	use v2\Database\Entity\Entry;
	use v2\Database\Entity\EntryDeveloper;
	use v2\Database\QueryBuilder;

	class BannedRepository extends Repository
	{
		/**
		 * Entity class of repository
		 */
		protected $entity = Banned::class;

		/**
		 * @param Entry $entry
		 * @param $ip
		 * @param $location
		 * @param $postal
		 * @return array
		 */
		public function findByIpOrEntryAndLocation($entry, $ip, $location, $postal = null)
		{
			$entry = is_int($entry) ? $entry : $entry->getId();

			$query = $this->select()
				->from(Banned::TABLE, 'b')
				->where('ip', 'IN', '(' . $ip .')');

			if ($postal !== null) {
				$query->orWhere('postal', '=', $postal, '(');
			} else {
				$query->orWhere('entry_id', '=', $entry, '(');
			}			

			$query->andWhere('location', '=', $location, '', ')');

			return $query->getResult();
		}

		public function findByIpAndDeveloper($ip, $developer)
		{
			$developer = is_int($developer) ? $developer : $developer->getId();

			$sql = $this->select('e.*')
				->from(Download::TABLE, 'd')
				->leftJoin(Entry::TABLE, 'e' , 'e.id', '=', 'd.entry_id')
				->leftJoin(EntryDeveloper::TABLE, 'ed', 'ed.entry_id', '=', 'e.id')
				->where('d.ip', '=', '"' . $ip . '"')
				->andWhere('ed.developer_id', '=', $developer)
				->andWhere('d.created', '>=', 'NOW() - INTERVAL 2 DAY')->getSQL();

			$result = $this->runQuery(null, null, $sql);

			$entries = [];
			while ($row = mysqli_fetch_assoc($result)) {
				$entries[] = $row;
			}
			return $entries;
		}
	}
?>