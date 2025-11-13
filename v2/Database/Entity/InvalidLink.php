<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 14-11-25
	 * Time: 12:00
	 */

	namespace v2\Database\Entity;

	use Exception;

	class InvalidLink extends Entity
	{
		/**
		 * @var string
		 */
		CONST TABLE = 'invalid_links';

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
		protected $link = null;

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
		public function getEntry(bool $onlyId = false)
		{
			return $this->getEntity($onlyId);
		}

		/**
		 * @param $entity
		 * @return InvalidLink
		 * @throws Exception
		 */
		public function setEntry($entity): InvalidLink
		{
			$this->entry = $this->setEntity($entity);

			return $this;
		}

		/**
		 * @param bool $onlyId
		 * @return mixed
		 */
		public function getLink(bool $onlyId = false)
		{
			return $this->getEntity($onlyId);
		}

		/**
		 * @param $entity
		 * @return InvalidLink
		 * @throws Exception
		 */
		public function setLink($entity): InvalidLink
		{
			$this->link = $this->setEntity($entity);

			return $this;
		}
	}