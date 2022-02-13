<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 26-11-19
	 * Time: 23:52
	 */

	namespace v2\Database\Entity;

	class Download extends Entity
	{
		/**
		 * @var string
		 */
		CONST TABLE = 'downloads';

		/**
		 * @var integer
		 */
		protected $id;

		/**
		 * @var string
		 */
		private $ip;

		/**
		 * @var mixed
		 */
		protected $entry = null;

		/**
		 * @var mixed
		 */
		protected $link = null;

		/**
		 * @var mixed
		 */
		private $time;

		/**
		 * @return int
		 */
		public function getId(): int
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
		 * @return Downloads
		 */
		public function setIp(string $ip): Download
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
		 * @return Downloads
		 * @throws \Exception
		 */
		public function setEntry($entity): Download
		{
			$this->entry = $this->setEntity($entity);

			return $this;
		}

		/**
		 * @param bool $onlyId
		 * @return mixed
		 */
		public function getLink($onlyId = false)
		{
			return $this->getEntity($onlyId);
		}

		/**
		 * @param $entity
		 * @return Downloads
		 * @throws \Exception
		 */
		public function setLink($entity): Download
		{
			$this->link = $this->setEntity($entity);

			return $this;
		}

		/**
		 * @return mixed
		 */
		public function getTime()
		{
			return $this->time;
		}

		/**
		 * @param $time
		 * @return Downloads
		 */
		public function setTime($time): Download
		{
			$this->time = $time;

			return $this;
		}

	}
?>