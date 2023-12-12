<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 1-12-19
	 * Time: 14:47
	 */

	namespace v2\Database\Repository;

	use v2\Database\Entity\Banned;
	use v2\Database\Entity\Entry;
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
		 * @return array
		 */
		public function findByIpOrEntryAndLocation($entry, $ip, $location)
		{
			$entry = is_int($entry) ? $entry : $entry->getId();

			return $this->select()
				->from(Banned::TABLE, 'b')
				->where('ip', 'IN', '(' . $ip .')')
				->orWhere('entry_id', '=', $entry, '(')
				->andWhere('location', '=', $location, '', ')')
				->getResult();
		}
	}
?>