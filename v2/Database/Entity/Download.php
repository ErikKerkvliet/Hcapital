<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 26-11-19
	 * Time: 23:52
	 */

	namespace v2\Database\Entity;

	use Exception;

    class Download extends Entity
	{
		/**
		 * @var string
		 */
		CONST TABLE = 'downloads';

        /**
         * @var string
         */
        const TO_MANY_DOWNLOADS_LINK = 'To many links downloaded';

        /**
         * @var string
         */
        const TO_MANY_DOWNLOADS_ENTRY = 'To many entries downloaded';

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
		 * @var string
		 */
		private $comment = null;

		/**
		 * @var mixed
		 */
		private $created;

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
		 * @return $this
		 */
		public function setIp(string $ip): self
		{
			$this->ip = $ip;

			return $this;
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
		 * @return $this
		 * @throws Exception
		 */
		public function setEntry($entity): self
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
		 * @return $this
		 * @throws Exception
		 */
		public function setLink($entity): self
		{
			$this->link = $this->setEntity($entity);

			return $this;
		}

		/**
		 * @return string
		 */
		public function getComment(): ?string
        {
			return $this->comment;
		}

        /**
         * @param string $comment
         * @return $this
         */
		public function setComment(string $comment): self
        {
			$this->comment = $comment;

			return $this;
		}

		/**
		 * @return mixed
		 */
		public function getCreated()
		{
			return $this->created;
		}

		/**
		 * @param $created
		 * @return $this
		 */
		public function setCreated($created): self
		{
			$this->created = $created;

			return $this;
		}
	}
