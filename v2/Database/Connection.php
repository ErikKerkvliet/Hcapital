<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 30-11-19
	 * Time: 1:10
	 */

	namespace v2\Database;

	class Connection extends QueryHandler
	{
		/**
		 * @var null|\mysqli
		 */
		private $connection = null;

		/**
		 * @var string
		 */
		protected $query = '';


		/**
		 * Connection constructor.
		 */
		public function __construct()
		{
			$this->setupConnection();
		}

		/**
		 * setup a connection with the database
		 */
		protected function setupConnection()
		{
			if (strpos($_SERVER['HTTP_HOST'], 'hcapital.tk') !== false) {
				$db = 'hcapital_tk_hcapita';
				$host = 'localhost';
				$user = 'hcapi_tk_hcapita';
				$password = 'dDqK7oDf6fVD';

				$this->connection = new \mysqli($host, $user, $password, $db);
			} else {
				$db = 'hcapital';
				$host = 'localhost';
				$user = 'yuuichi_sagara';
				$password = 'firefly';

				$this->connection = new \mysqli($host, $user, $password, $db);

				if (! isset($GLOBALS['set'])) {
					$GLOBALS['set'] = 1;

					$this->query = "set global sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'";

					$this->runQuery(null, $this->connection);

					$this->query = "set session sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'";

					$this->runQuery(null, $this->connection);
				}
			}
			mysqli_set_charset($this->connection,"utf8");

			mysqli_query($this->connection, 'SET CHARACTER SET sjis');

			if ($this->connection->connect_errno) {
				echo "Failed to connect to MySQL: (" . $this->connection->connect_errno . ") " . $this->connection->connect_error;
			}
		}

		public function getConnection()
		{
			return $this->connection;
		}
	}
