<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 26-11-19
	 * Time: 23:59
	 */

	namespace v2\Database\Entity;

	class ToDo extends Entity
	{
		/**
		 * @var string
		 */
		CONST TABLE = 'todo';

		/**
		 * @var integer
		 */
		protected $id;

		/**
		 * @var mixed
		 */
		protected $entry = null;

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
		 * @return ToDo
		 * @throws \Exception
		 */
		public function setEntry($entity): ToDo
		{
			$this->entry = $this->setEntity($entity);

			return $this;
		}
	}
?>
