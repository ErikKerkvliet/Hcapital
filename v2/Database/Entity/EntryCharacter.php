<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 26-11-19
	 * Time: 23:43
	 */

	namespace v2\Database\Entity;

	use Exception;
    use v2\Manager;

	class EntryCharacter extends Entity
	{
		/**
		 * @var string
		 */
		CONST TABLE = Manager::TEST ? 'entry_characters_2' : 'entry_characters';

		/**
		 * @var integer
		 */
		protected $id;

		/**
		 * @var mixed
		 */
		protected $character = null;

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
		public function getCharacter(bool $onlyId = false)
		{
			return $this->getEntity($onlyId);
		}

		/**
		 * @param $entity
		 * @return EntryCharacter
		 * @throws Exception
		 */
		public function setCharacter($entity): EntryCharacter
		{
			$this->character = $this->setEntity($entity);

			return $this;
		}

		/**
		 * @return mixed
		 */
		public function getEntry($onlyId = false)
		{
			return $this->getEntity($onlyId);
		}

		/**
		 * @param $entity
		 * @return EntryCharacter
		 * @throws Exception
		 */
		public function setEntry($entity): EntryCharacter
		{
			$this->entry = $this->setEntity($entity);

			return $this;
		}
	}
