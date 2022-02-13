<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 19-8-20
	 * Time: 23:28
	 */

	namespace v2\Database\Entity;


	class SharingThread extends Entity
	{
		/**
		 * @var string
		 */
		CONST TABLE = 'sharing_threads';

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
		protected $title = '';

		/**
		 * @var mixed
		 */
		protected $author = '';

		/**
		 * @var mixed
		 */
		protected $type = '';

		/**
		 * @var string
		 */
		protected $url = '';

		/**
		 * @var boolean
		 */
		protected $confirmed = 0;

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
		public function setEntry($entity): SharingThread
		{
			$this->entry = $this->setEntity($entity);

			return $this;
		}

		/**
		 * @return mixed
		 */
		public function getTitle()
		{
			return $this->title;
		}

		/**
		 * @param mixed $title
		 * @return SharingThread
		 */
		public function setTitle($title): SharingThread
		{
			$this->title = $title;
			return $this;
		}

		/**
		 * @return mixed
		 */
		public function getAuthor()
		{
			return $this->author;
		}

		/**
		 * @param mixed $author
		 * @return SharingThread
		 */
		public function setAuthor($author): SharingThread
		{
			$this->author = $author;
			return $this;
		}

		/**
		 * @return mixed
		 */
		public function getType()
		{
			return $this->type;
		}

		/**
		 * @param mixed $type
		 * @return SharingThread
		 */
		public function setType($type): SharingThread
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
		 * @return SharingThread
		 */
		public function setUrl(string $url): SharingThread
		{
			$this->url = $url;
			return $this;
		}

		/**
		 * @return bool
		 */
		public function getConfirmed(): bool
		{
			return $this->confirmed;
		}

		/**
		 * @param bool $confirmed
		 * @return SharingThread
		 */
		public function setConfirmed(bool $confirmed): SharingThread
		{
			$this->confirmed = $confirmed;
			return $this;
		}
	}
