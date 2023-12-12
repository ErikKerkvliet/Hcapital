<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 16-8-20
	 * Time: 14:19
	 */

	namespace v2\Database\Entity;


	class Thread extends Entity
	{
		/**
		 * @var string
		 */
		CONST TABLE = 'threads';

		/**
		 * @var integer
		 */
		protected $id;

		/**
		 * @var mixed
		 */
		protected $entry = null;

		/**
		 * @var string
		 */
		protected $type = '';

		/**
		 * @var string
		 */
		protected $url;

		/**
		 * @var string
		 */
		protected $author = '';

		/**
		 * @var boolean
		 */
		protected $confirmed = false;

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
			try{
				$this->getEntity($onlyId);
			} catch (\Exception $e) {
				dd(144);
			}
			return $this->getEntity($onlyId);
		}

		/**
		 * @param $entity
		 * @return EntryRelation
		 * @throws /Exception
		 */
		public function setEntry($entity)
		{
			$this->entry = $this->setEntity($entity);

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
		 * @return Thread
		 */
		public function setType(string $type): Thread
		{
			$this->type = $type;
			return $this;
		}

		/**
		 * @return string
		 */
		public function getUrl(): string
		{
			return $this->url;
		}

		/**
		 * @param string $url
		 * @return Thread
		 */
		public function setUrl(string $url): Thread
		{
			$this->url = $url;

			return $this;
		}

		/**
		 * @return string
		 */
		public function getAuthor(): string
		{
			return $this->author;
		}

		/**
		 * @param string $author
		 * @return Thread
		 */
		public function setAuthor(string $author): Thread
		{
			$this->author = $author;
			return $this;
		}

		/**
		 * @return boolean
		 */
		public function getConfirmed(): bool
		{
			return (bool) $this->confirmed;
		}

		/**
		 * @param bool $confirmed
		 * @return Thread
		 */
		public function setConfirmed($confirmed): Thread
		{
			$this->confirmed = $confirmed;

			return $this;
		}
	}
