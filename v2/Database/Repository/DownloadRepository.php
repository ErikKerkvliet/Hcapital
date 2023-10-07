<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 27-12-19
	 * Time: 21:53
	 */

	namespace v2\Database\Repository;


	use v2\Database\Entity\Download;

	class DownloadRepository extends Repository
	{
		/**
		 * Entity class of repository
		 */
		protected $entity = Download::class;

		public function deleteResults($date = null, $entry = null)
		{
			$query = 'DELETE FROM downloads';

			if (empty($date) && empty($entry)) {
				$this->runQuery($query);
				return;
			}

			$where = [];
			if (is_numeric($entry)) {
				$where[] = "entry_id = " . $entry;
			}
			if ($date) {
				$where[] = "time = '" . $date . "'";
			}

			$query .= ' WHERE ' . implode(' AND ', $where);

			$this->runQuery(null, null, $query);
		}
	}