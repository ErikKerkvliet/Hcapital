<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 28-12-19
	 * Time: 17:14
	 */

	namespace v2\Database;


	use v2\Database\Entity\Entry;

	class QueryBuilder extends QueryHandler
	{
		/**
		 * @var string
		 */
		protected $query = '';

		/**
		 * @var bool|string $count
		 */
		private $count = false;

		/**
		 * QueryBuilder constructor.
		 */
		public function __construct()
		{
		}

		/**
		 * @param string $variables
		 * @return $this
		 */
		public function select($variables = '')
		{
			$variables = $variables ?: '*';
			$this->query .= 'SELECT ' . $variables . ' ';

			return $this;
		}

		/**
		 * @param $table
		 * @param $alias
		 * @return $this
		 */
		public function from($table = '', $alias = '')
		{
			if (is_object($table)) {
				$table = $table::TABLE;
			}
			$this->query .= 'FROM ' . $table . ' ' . $alias . ' ';

			return $this;
		}

		/**
		 * @param $value1
		 * @param $condition
		 * @param $value2
		 * @param string $ob
		 * @param string $eb
		 * @return $this
		 */
		public function where($value1, $condition, $value2, $ob = '', $eb = '')
		{
			$this->query .= 'WHERE ';

			$this->whereSingle($value1, $condition, $value2, $ob, $eb);

			return $this;
		}

		/**
		 * @param $value1
		 * @param $condition
		 * @param $value2
		 * @param string $ob
		 * @param string $eb
		 * @return $this
		 */
		public function andWhere($value1, $condition, $value2, $ob = '', $eb = '')
		{
			$this->query .= 'AND ';

			$this->whereSingle($value1, $condition, $value2, $ob, $eb);

			return $this;
		}

		/**
		 * @param $value1
		 * @param $condition
		 * @param $value2
		 * @param string $ob
		 * @param string $eb
		 * @return $this
		 */
		public function orWhere($value1, $condition, $value2, $ob = '', $eb = '')
		{
			$this->query .= 'OR ';

			$this->whereSingle($value1, $condition, $value2, $ob, $eb);

			return $this;
		}


		/**
		 * @param string $query
		 * @return $this
		 */
		public function whereQuery($type, $query)
		{
			$this->query .= $type . ' ' . $query . ' ';

			return $this;
		}

		/**
		 * @param $value1
		 * @param $condition
		 * @param $value2
		 * @param string $ob
		 * @param string $eb
		 */
		public function whereSingle($value1, $condition, $value2, $ob = '', $eb = '')
		{
			$this->query .= $ob . $value1 . ' ' . $condition . ' ' . $value2 . $eb . ' ';
		}

		/**
		 * @return $this
		 */
		public function or()
		{
			$this->query .= ' OR ';

			return $this;
		}

		/**
		 * @return $this
		 */
		public function and()
		{
			$this->query .= ' AND ';

			return $this;
		}

		/**
		 * @param $table
		 * @param $alias
		 * @param $value1
		 * @param $condition
		 * @param $value2
		 * @return $this
		 */
		public function leftJoin($table, $alias, $value1, $condition, $value2)
		{
			$this->query .= ' LEFT JOIN ' . $table . ' ' . $alias . ' ON ' . $value1 . ' ' . $condition . ' ' . $value2 . ' ';

			return $this;
		}

		/*
		* @param string $query
		 * @return $this	
		*/
		public function innerJoinQuery(string $query)
		{
			$this->query .= ' INNER JOIN ' . $query . ' ';

			return $this;
		}

		/**
		 * @param $column
		 * @param $sort
		 * @return $this
		 */
		public function orderBy($column, $sort)
		{
			$this->query .= ' ORDER BY ' . $column . ' ' . $sort . ' ';

			return $this;
		}

		/**
		 * @param $column
		 * @return $this
		 */
		public function groupBy($column)
		{
			$this->query .= ' GROUP BY ' . $column . ' ';

			return $this;

		}

		/**
		 * @param $start
		 * @param $size
		 * @return $this
		 */
		public function limit($start, $size = null)
		{
			$this->query .= ' LIMIT ' . $start . ' ';

			if ($size) {
				$this->query .= ' , ' . $size . ' ';
			}

			return $this;
		}

		/**
		 * @return $this
		 */
		public function ob()
		{
			$this->query .= ' ( ';

			return $this;
		}

		/**
		 * @return $this
		 */
		public function eb()
		{
			$this->query .= ' ) ';

			return $this;
		}

		/**
		 * @return $this
		 */
		public function as($alias)
		{
			$this->query .= ' AS ' . $alias;

			return $this;
		}

		public function regexp($column, $value)
		{
			$value = str_replace('"', '\"', $value);
			$value = str_replace("'", "\'", $value);

			$this->query .= ' WHERE ' . $column . ' REGEXP "' . $value . '"';

			return $this;
		}

		/**
		 * @return $this
		 */
		public function unionAll()
		{
			$this->query .= ' UNION ALL ';

			return $this;
		}

		/**
		 * @return $this
		 */
		public function union()
		{
			$this->query .= ' UNION ';

			return $this;
		}

		/**
		 * @return string
		 */
		public function getSQL()
		{
			return $this->query;
		}

		/**
		 * @param $sql
		 * @return $this
		 */
		public function addRaw($sql)
		{
			$this->query .= $sql . ' ';

			return $this;
		}

		/**
		 * @return $this
		 */
		public function clear()
		{
			$this->query = '';

			return $this;
		}

		/**
		 * @return $this
		 */
		public function dq()
		{
			dd($this->getSQL());

			return $this;
		}

		/**
		 * @param null $entity
		 * @return array|int|mixed
		 */
		public function getResult($entity = null)
		{
			$originalValue = $entity;

			$entity = $entity ?: $this->entity;

			if ($originalValue === false) {
				$entity = null;
			}

			return $this->runQuery($entity);
		}
	}