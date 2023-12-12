<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 26-11-19
	 * Time: 23:10
	 */

	namespace v2\Database\Entity;

	use v2\Manager;

	class Entry extends Entity
	{
		/**
		 * @var string
		 */
		CONST TABLE = 'entries';

		/**
		 * @var string
		 */
		CONST COVER_M = '_cover_m.jpg';

		/**
		 * @var string
		 */
		CONST COVER_L = '_cover_l.jpg';

		/**
		 * @var int
		 */
		protected $id = 0;

		/**
		 * @var string
		 */
		private $title = '';

		/**
		 * @var string
		 */
		private $romanji = '';

		/**
		 * @var null|string
		 */
		private $released = '0000-00-00';

		/**
		 * @var null|string
		 */
		private $size = '';

		/**
		 * @var null|string
		 */
		private $website = '';

		/**
		 * @var null|string
		 */
		private $information = '';

		/**
		 * @var null|string
		 */
		private $password = '';

		/**
		 * @var null|string
		 */
		private $type = 'game';

		/**
		 * @var null|string
		 */
		private $timeType = 'new';

		/**
		 * @var mixed
		 */
		private $lastEdit = '';

		/**
		 * @var mixed
		 */
		private $created = '';

		/**
		 * @var integer
		 */
		private $downloads = 0;


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
		public function getTitle(): string
		{
			return $this->title;
		}

		/**
		 * @param $title
		 * @return Entry
		 */
		public function setTitle($title): Entry
		{
			$this->title = $title;

			return $this;
		}

		/**
		 * @return string
		 */
		public function getRomanji(): string
		{
			return $this->romanji;
		}

		/**
		 * @param string $romanji
		 * @return Entry
		 */
		public function setRomanji(string $romanji): Entry
		{
			$this->romanji = $romanji;

			return $this;
		}

		/**
		 * @return string
		 */
		public function getReleased(): string
		{
			return $this->released;
		}

		/**
		 * @param string $released
		 * @return Entry
		 */
		public function setReleased(string $released): Entry
		{
			$this->released = $released;

			return $this;
		}

		/**
		 * @return string
		 */
		public function getSize(): string
		{
			return $this->size;
		}

		/**
		 * @param string $size
		 * @return Entry
		 */
		public function setSize(string $size): Entry
		{
			$this->size = $size;

			return $this;
		}

		/**
		 * @return string
		 */
		public function getWebsite(): string
		{
			return $this->website;
		}

		/**
		 * @param string $website
		 * @return Entry
		 */
		public function setWebsite(string $website): Entry
		{
			$this->website = $website;

			return $this;
		}

		/**
		 * @return string
		 */
		public function getInformation(): string
		{
			return $this->information;
		}

		/**
		 * @param string $information
		 * @return Entry
		 */
		public function setInformation(string $information): Entry
		{
			$this->information = $information;

			return $this;
		}

		/**
		 * @return string
		 */
		public function getPassword(): string
		{
			return $this->password;
		}

		/**
		 * @param string $password
		 * @return Entry
		 */
		public function setPassword(string $password): Entry
		{
			$this->password = $password;

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
		 * @return Entry
		 */
		public function setType(string $type): Entry
		{
			$this->type = $type;

			return $this;
		}

		/**
		 * @return string
		 */
		public function getTimeType(): string
		{
			return $this->timeType;
		}

		/**
		 * @param string $timeType
		 * @return Entry
		 */
		public function setTimeType(string $timeType): Entry
		{
			$this->timeType = $timeType;

			return $this;
		}

		/**
		 * @return mixed
		 */
		public function getLastEdit()
		{
			return $this->lastEdit;
		}

		/**
		 * @param $lastEdit
		 * @return Entry
		 */
		public function setLastEdit($lastEdit): Entry
		{
			$this->lastEdit = $lastEdit;

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
		 * @return Entry
		 */
		public function setCreated($created): Entry
		{
			$this->created = $created;

			return $this;
		}

		/**
		 * @return int
		 */
		public function getDownloads(): int
		{
			return $this->downloads;
		}

		/**
		 * @param int $downloads
		 * @return Entry
		 */
		public function setDownloads(int $downloads): Entry
		{
			$this->downloads = $downloads;

			return $this;
		}
	}
?>