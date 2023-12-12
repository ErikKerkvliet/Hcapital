<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 26-11-19
	 * Time: 23:57
	 */

	namespace v2\Database\Entity;

	/**
	 * Class EntryRelation
	 */
	class EntryRelation extends Entity
	{
		/**
		 * @var string
		 */
		CONST TABLE = 'entry_relations';

		/**
		 * @var integer
		 */
		protected $id;

		/**
		 * @var mixed
		 */
		protected $entry = null;

		/**
		 * @var mixed
		 */
		protected $relation = null;

		/**
		 * @var string
		 */
		private $type;

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
		public function getEntry($onlyId = false)
		{
			return $this->getEntity($onlyId);
		}

		/**
		 * @param $entity
		 * @return EntryRelation
		 * @throws /Exception
		 */
		public function setEntry($entity): EntryRelation
		{
			$this->entry = $this->setEntity($entity);

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
		 * @return EntryRelation
		 * @throws /Exception
		 */
		public function setRelation($entity): EntryRelation
		{
			$this->relation = $this->setEntity($entity);

			return $this;
		}

		/**
		 * @return string
		 */
		public function getType(): string
		{
			return $this->type;
		}

		/**
		 * @param string $type
		 * @return EntryRelation
		 */
		public function setType(string $type): EntryRelation
		{
			$this->type = $type;

			return $this;
		}
	}
?>