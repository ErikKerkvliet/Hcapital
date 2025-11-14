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

		public function deleteDownloads($entry = null, $date = null)
		{
			$query = 'DELETE FROM downloads';

			if (empty($date) && empty($entry)) {
				$this->runQuery(null, null, $query);
				return;
			}

			$where = [];
			if (is_numeric($entry)) {
				$where[] = "entry_id = " . $entry;
			}
			if ($date) {
				$where[] = "created < '" . $date . "'";
			}

			$query .= ' WHERE ' . implode(' AND ', $where);

			$this->runQuery(null, null, $query);
		}

        public function getDownloadsByIp(string $ip, int $intervalInDays = 0): array
        {
            $query = sprintf("SELECT DISTINCT id FROM downloads WHERE ip = '%s' AND created >= DATE_SUB(NOW(), INTERVAL %d day);",
                $ip,
                $intervalInDays
            );

            return $this->addRaw($query)
                ->getResult();
        }

		public function findIpsByLinkInLastDays(int $linkId, int $days = 30): array
		{
			$query = sprintf("SELECT DISTINCT ip FROM downloads WHERE link_id = %d AND created >= DATE_SUB(NOW(), INTERVAL %d DAY);",
				$linkId,
				$days
			);

			$result = $this->runQuery(null, null, $query);

			if ($result === false) {
				return [];
			}

			$ips = [];
			while ($row = mysqli_fetch_assoc($result)) {
				$ips[] = $row['ip'];
			}
			return $ips;
		}
	}