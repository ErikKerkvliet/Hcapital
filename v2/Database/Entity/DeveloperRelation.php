<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 26-11-19
	 * Time: 23:46
	 */

	namespace v2\Database\Entity;

	class DeveloperRelation extends Entity
	{
		/**
		 * @var string
		 */
		CONST TABLE = 'developer_relations';

		/**
		 * @var integer
		 */
		protected $id;

		/**
		 * @var mixed
		 */
		protected $developer = null;

		/**
		 * @var mixed
		 */
		protected $relation = null;

		/**
		 * @return int
		 */
		public function getId(): int
		{
			return $this->id;
		}

		/**
		 * @param bool $onlyId
		 * @return mixed
		 */
		public function getDeveloper($onlyId = false)
		{
			return $this->getEntity($onlyId);
		}

		/**
		 * @param $entity
		 * @return DeveloperRelation
		 * @throws \Exception
		 */
		public function setDeveloper($entity): DeveloperRelation
		{
			$this->developer = $this->setEntity($entity);

			return $this;
		}

		/**
		 * @param bool $onlyId
		 * @return mixed
		 */
		public function getRelation($onlyId = false)
		{
			return $this->getEntity($onlyId);
		}

		/**
		 * @param $entity
		 * @return DeveloperRelation
		 * @throws \Exception
		 */
		public function setRelation($entity): DeveloperRelation
		{
			$this->relation = $this->setEntity($entity);

			return $this;
		}
	}
?>