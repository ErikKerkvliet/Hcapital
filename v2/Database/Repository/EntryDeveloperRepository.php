<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 5-2-20
	 * Time: 21:20
	 */

	namespace v2\Database\Repository;


	use v2\Database\Entity\Developer;
	use v2\Database\Entity\EntryDeveloper;

	class EntryDeveloperRepository extends Repository
	{
		/**
		 * @var string
		 */
		protected $entity = EntryDeveloper::class;

		public function findDevelopersByEntry($entry)
		{
			$entry = is_numeric($entry) ? $entry : $entry->getId();
			return $this->select('d.*')
				->from(Developer::TABLE, 'd')
				->leftJoin(EntryDeveloper::TABLE, 'ed', 'ed.developer_id', '=', 'd.id')
				->where('ed.entry_id', '=', $entry)
				->getResult(Developer::class);
		}
	}