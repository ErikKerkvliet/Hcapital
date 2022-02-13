<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 26-11-19
	 * Time: 23:29
	 */

	namespace v2\Database\Entity;

	class Banned extends Entity
	{
		/**
		 * @var string
		 */
		CONST TABLE = 'banned';

		/**
		 * @var int
		 */
		protected $id = null;

		/**
		 * @var string
		 */
		private $ip = '';

		/**
		 * @var mixed
		 */
		protected $entry = null;

		/**
		 * @var string
		 */
		private $location = '';

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
		 * @return Banned
		 */
		public function setIp(string $ip): Banned
		{
			$this->ip = $ip;

			return $this;
		}

		/**
		 * @param bool $onlyId
		 * @return mixed
		 */
		public function getEntry($onlyId= false)
		{
			return $this->getEntity($onlyId);
		}

		/**
		 * @param $entity
		 * @return Banned
		 * @throws \Exception
		 */
		public function setEntry($entity): Banned
		{
			$this->entry = $this->setEntity($entity);

			return $this;
		}

		/**
		 * @return string
		 */
		public function getLocation(): string
		{
			return $this->location;
		}

		/**
		 * @param string $location
		 * @return Banned
		 */
		public function setLocation(string $location): Banned
		{
			$this->location = $location;

			return $this;
		}
	}
?>