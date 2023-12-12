<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 26-11-19
	 * Time: 23:34
	 */

	namespace v2\Database\Entity;

	class Broken extends Entity
	{
		/**
		 * @var string
		 */
		CONST TABLE = 'broken';

		/**
		 * @var int
		 */
		protected $id = null;

		/**
		 * @var string
		 */
		private $ip;

		/**
		 * @var mixed
		 */
		protected $entry = null;

		/**
		 * @var string
		 */
		private $email;


		/**
		 * @return int|null
		 */
		public function getId()
		{
			return $this->id;
		}

		/**
		 * @return string
		 */
		public function getIp(): string
		{
			return $this->ip;
		}

		/**
		 * @param string $ip
		 * @return Broken
		 */
		public function setIp(string $ip): Broken
		{
			$this->ip = $ip;

			return $this;
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
		 * @return Broken
		 * @throws \Exception
		 */
		public function setEntry($entity): Broken
		{
			$this->entry = $this->setEntity($entity);

			return $this;
		}

		/**
		 * @return string
		 */
		public function getEmail(): string
		{
			return $this->email;
		}

		/**
		 * @param string $email
		 * @return Broken
		 */
		public function setEmail(string $email): Broken
		{
			$this->email = $email;

			return $this;
		}
	}
?>