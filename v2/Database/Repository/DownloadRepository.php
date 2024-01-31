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
				$where[] = "created = '" . $date . "'";
			}

			$query .= ' WHERE ' . implode(' AND ', $where);

			$this->runQuery(null, null, $query);
		}

        public function deleteOld()
        {
            $query = "DELETE FROM downloads WHERE created < DATE_SUB(NOW(), INTERVAL 1 month);";

            $this->runQuery(null, null, $query);
        }

        public function getDownloadsByIp(string $ip, int $intervalInDays = 0): array
        {
            $query = sprintf("SELECT * FROM downloads WHERE ip = '%s' AND created >= DATE_SUB(NOW(), INTERVAL %d day);",
                $ip,
                $intervalInDays
            );

            return $this->addRaw($query)
                ->getResult();
        }
	}