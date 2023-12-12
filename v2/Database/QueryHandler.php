<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 1-12-19
	 * Time: 14:31
	 */

	namespace v2\Database;


	use v2\Classes\AdminCheck;
	use v2\Manager;

	class QueryHandler
	{
		/**
		 * @var array
		 */
		protected $results = [];

		/**
		 * @param null $entity
		 * @param Connection|null $connection
		 * @param null $query
		 * @return mixed
		 */
		protected function runQuery($entity = null, $connection = null, $query = null)
		{
			if (! $this->results) {
				$this->results[] = 'Start';
			}

			if (! $connection) {
				$connection = app('connection');
			}

			if (substr($this->query, -1, 1) != ';') {
				$this->query .= ';';
			}

			$query = $query ?: $this->query;
			$this->query = '';
			if (AdminCheck::checkForAdmin()) {
//				if (strpos(strtoupper($query), 'SELECT LAST_INSERT_ID()') !== false) {
//					dc($query);
//					return ['id' => 99999];
//				}
//				if (strpos(strtoupper($query), 'INSERT') !== false) {
//					dd($query);
//					return 99999;
//				}
//				if (strpos(strtoupper($query), 'UPDATE') !== false) {
//					dd($query);
//					return 99999;
//				}
//				if (strpos(strtoupper($query), 'DELETE') !== false) {
//					dd($query);
//				}
//				if (Manager::TEST) {
//					dc($query);
//				}
			}
			if (Manager::TEST) {
				logQuery($query, $connection, 'INSERT');
			}
			mysqli_set_charset($connection,"utf8");

			mb_regex_encoding('UTF-8');
			mb_internal_encoding('UTF-8');
			//dd($query);
			$result = mysqli_query($connection, $query);
			$this->results[] = ['query' => $query, 'result' => $result];

			if ($entity) {
				$entities = app('em')->handleResults($entity, $result);

				return $entities;
			}
			return $result;
		}
	}
?>
