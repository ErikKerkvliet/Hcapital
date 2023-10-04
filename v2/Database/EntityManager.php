<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 27-11-19
	 * Time: 18:39
	 */

	namespace v2\Database;

	use v2\Database\Entity\Developer;
	use v2\Database\Entity\DeveloperRelation;
	use v2\Database\Entity\EntryRelation;
	use v2\Database\Entity\Link;

	class EntityManager extends Connection
	{
		/**
		 * @var EntityManager
		 */
		protected $em;

		/**
		 * @var string
		 */
		private $entity = '';

		/**
		 * @var array
		 */
		private $entities = [];

		/**
		 * @var array
		 */
		private $queries = [];

		/**
		 * Dump result variable
		 */
		public function dumpResults()
		{
			var_dump($this->results);
			dc(count($this->results));
			foreach ($this->results as $result) {
				foreach ($result as $item) {
					try {
						dc($item);
					} catch (\Exception $e) {
						dd($item);
					}
				}
			}
			dd();
		}

		/**
		 * @param $entityClass
		 */
	    private function includeEntity()
	    {
		    $exploded = explode('\\', $this->entity);
		    $entity = end($exploded);

			$fileName = $GLOBALS['source'] == 'ajax' ? 'Database/Entity/' . $entity . '.php' :
				'v2/Database/Entity/' . $entity . '.php';

	    	require_once($fileName);
	    }

		/**
		 * @param $entityClass
		 */
	    private function includeRepository()
	    {
		    $exploded = explode('\\', $this->entity);
		    $entity = end($exploded);

		    $fileName = $GLOBALS['source'] == 'ajax' ? 'Database/Repository/' . $entity . 'Repository.php' :
			    'v2/Database/Repository/' . $entity . 'Repository.php';

		    require_once($fileName);
	    }

		/**
		 * @param $entityClass
		 * @return mixed
		 */
	    public function getRepository($entityClass)
	    {
		    $this->entity = $entityClass;

		    $this->includeEntity();
	    	$this->includeRepository();

		    $entityClass = str_replace('Entity', 'Repository', $entityClass);
		    $repository = $entityClass . 'Repository';

	    	return new $repository($this, $this->entity);
	    }

		/**
		 * @param $entityClass
		 * @param $id
		 * @return mixed
		 */
	    public function find($entityClass, $entry)
	    {
	    	$id = is_numeric($entry) ? $entry : $entry->getId();

		    $this->entity = $entityClass;

		    $this->em = $this;

		    $this->includeEntity();

		    $this->query = "SELECT * FROM " . $this->entity::TABLE . " WHERE id = " . $id;

		    $result = $this->runQuery($this->entity);

		    return $result ? $result[0] : [];
	    }

		/**
		 * @param Object $entity
		 */
		public function persist(Object $entity)
		{
			$functions = $this->getFunctions($entity);
			$columns = [];
			$values = [];
			array_map(function ($function) use ($entity, &$columns, &$values) {
				$functionName = 'get' . $function;
				$value = $entity->{$functionName}();
				if ($value) {
					$columns[] = $this->reverseMapFunctionName(lcfirst($function));
					if (is_bool($value)) {
						$values[] = $value;
					} else {
						$values[] = mysqli_real_escape_string(app('connection'), $value);
					}
				}
			}, $functions);

			$query = "INSERT INTO " . $entity::TABLE;
			$query .= " (" . implode(", ", $columns) . ") VALUES (";
			$keys = array_keys($values);
			$endKey = end($keys);

			foreach ($values as $key => $value) {
				$query .= is_numeric($value) ? $value :
					(is_bool($value) ? "'" . (int) $value . "'" : "'" . $value . "'");

				$query .= $key != $endKey ? ',     ' : ')';
			}

			$this->queries[] = $query;
		}

		public function update(Object $entity)
		{
			$functions = $this->getFunctions($entity, true);
			$sets = [];
			array_filter(array_map(function ($function) use ($entity, &$sets){
				$functionName = 'get' . $function;
		        $value = $entity->{$functionName}(true);

				$function = lcfirst($function);
				if ($value !== $entity->getOriginalValues()[$function]) {
			        $columnName = $this->reverseMapFunctionName($function);
			        $sets[] = $columnName . ' = "' . $value . '"';
		        }
		    }, $functions));

			if (count($sets) > 0) {
				$query = 'UPDATE ' . $entity::TABLE . ' SET ' . implode(', ', $sets) . ' ';
				$query .= 'WHERE id = ' . $entity->getId();
				$this->queries[] = $query;
			}
		}

		/**
		 * @param int|Object $entity
		 */
		public function delete($entity)
		{
			$id = is_int($entity) ? $entity : $entity->getId();

		    $query = 'DELETE FROM ' . $entity::TABLE . ' WHERE id = ' . $id;
			$this->queries = [];
		    $this->queries[] = $query;

		    $this->runQuery(null, null, $query);

		    $this->queries = [];
		}

		/**
		 * @param Object|null $entity
		 * @param boolean $returnId
		 * @return array|null
		 */
		public function flush($entity = null, $returnId = false)
		{
			if ($entity) {
				$this->queries = [];

				$this->persist($entity);
			}
			foreach ($this->queries as $query) {
				$result = $this->runQuery(null, null, $query);
			}
			$this->queries = [];

			if ($returnId) {
				$idQuery = "SELECT LAST_INSERT_ID() AS id;";

				$result = $this->runQuery(null, null, $idQuery);
			}

			while (! is_bool($result) && $row = mysqli_fetch_assoc($result)) {
				return $row['id'];
			}
			if (is_array($result) && $result['id']) {
				return $result['id'];
			}

			return [];
		}

		/**
		 * @param $entityClass
		 * @param $result
		 * @return array
		 */
		public function handleResults($entityClass, $result): array
		{
			$keys = [];
			$entities = [];
			if (! $result) {
				return [];
			}
			while ($row = mysqli_fetch_assoc($result)) {
				$originalValues = ['id' => $row['id']];

				$entity = new $entityClass($row['id']);
				if (! $entity) {
					continue;
				}
				unset($row['id']);
				$keys = $keys ?: array_keys($row);

				foreach ($keys as $key) {
					if ($row[$key] === null) {
						continue;
					}
					$name = $this->mapFunctionName($key);

					$function = 'set' . ucfirst($name);

					$entity->{$function}($row[$key]);

					$originalValues = array_merge($originalValues, [$name => $row[$key]]);
//					if(strpos(strtolower($entityClass), 'character')) {
//						dc($key);
//					}
				}

				$entity->setInitialized(true);

				$entity->setOriginalValues($originalValues);

				$entities[] = $entity;
			}
			return $entities;
		}

		private function getFunctions($entity, $excludeId = false)
		{
			$exclude = [];
			$exclude[] = 'getOriginalValues';
			if ($excludeId) {
				$exclude[] = 'getId';
			}
			$functions = [];

			array_map(function ($function) use (&$functions, $exclude) {
				if (substr($function, 0, 3) == 'get' && ! in_array($function, $exclude)) {
					$functions[] = substr($function, 3);
				}
			}, get_class_methods($entity));

			return $functions;
		}

		/**
		 * @param String $name
		 * @param $entityClass
		 * @return string
		 */
	    private function mapFunctionName(String $name): string
	    {
		    switch ($name) {
			    case 'developer_id':
				    return 'developer';
				case 'relation_id':
					return 'relation';
				case 'entry_id':
					return 'entry';
				case 'character_id':
					return 'character';
				case 'link_id':
					return 'link';
			    case 'time_type':
				    return 'timeType';
			    case 'type':
				    return 'type';
			    case 'last_edited':
				    return 'lastEdit';
			    default:
				    return $name;
		    }
	    }

		/**
		 * @param String $name
		 * @param null $table
		 * @return string
		 */
		public function reverseMapFunctionName(String $name): string
		{
			switch ($name) {
				case 'entry':
					return 'entry_id';
				case 'character':
					return 'character_id';
				case 'developer':
					return 'developer_id';
				case 'relation':
					return 'relation_id';
				case 'timeType':
					return 'time_type';
				case 'edited':
					return 'last_edited';
				case 'lastEdit':
					return 'last_edited';
				default:
					return $name;
			}
		}
	}
?>
