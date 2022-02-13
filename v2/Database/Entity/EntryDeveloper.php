<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 5-2-20
	 * Time: 21:14
	 */

	namespace v2\Database\Entity;


	use v2\Manager;

	class EntryDeveloper extends Entity
	{
		/**
		 * @var string
		 */
		CONST TABLE = Manager::TEST ? 'entry_developers_2' : 'entry_developers';

		/**
		 * @var
		 */
		protected $id;

		/**
		 * @var mixed
		 */
		protected $entry = null;

		/**
		 * @var mixed
		 */
		protected $developer = null;


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
		 * @param mixed $entry
		 * @return EntryDeveloper
		 */
		public function setEntry($entry): EntryDeveloper
		{
			$this->entry = $entry;

			return $this;
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
		 * @param mixed $developer
		 * @return EntryDeveloper
		 */
		public function setDeveloper($developer): EntryDeveloper
		{
			$this->developer = $developer;

			return $this;
		}

	}