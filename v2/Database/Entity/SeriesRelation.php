<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 18-1-20
	 * Time: 17:17
	 */

	namespace v2\Database\Entity;


	class SeriesRelation extends Entity
	{
		/**
		 * @var string
		 */
		CONST TABLE = 'SerieRelation';

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
		protected $id = null;

		/**
		 * @var null
		 */
		private $entry = null;

		/**
		 * @var null
		 */
		private $relation = null;

		/**
		 * @var string
		 */
		private $title = '';

		/**
		 * @var string
		 */
		private $romanji = '';

		/**
		 * @var mixed
		 */
		private $released = '';

		/**
		 * @return int
		 */
		public function getId(): int
		{
			return $this->id;
		}

		/**
		 * @return int
		 */
		public function getEntry()
		{
			return $this->entry;
		}

		/**
		 * @param int $entry
		 * @return SeriesRelation
		 */
		public function setEntry($entry): SeriesRelation
		{
			$this->title = $entry;

			return $this;
		}

		/**
		 * @return int
		 */
		public function getRelation()
		{
			return $this->relation;
		}

		/**
		 * @param int $relation
		 * @return SeriesRelation
		 */
		public function setRelation($relation): SeriesRelation
		{
			$this->relation = $relation;

			return $this;
		}

		/**
		 * @return string
		 */
		public function getTitle(): string
		{
			return $this->title;
		}

		/**
		 * @param string $title
		 * @return SeriesRelation
		 */
		public function setTitle(string $title): SeriesRelation
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
		 * @return SeriesRelation
		 */
		public function setRomanji(string $romanji): SeriesRelation
		{
			$this->romanji = $romanji;

			return $this;
		}

		/**
		 * @return mixed
		 */
		public function getReleased()
		{
			return $this->released;
		}

		/**
		 * @param $released
		 * @return SeriesRelation
		 */
		public function setReleased($released): SeriesRelation
		{
			$this->released = $released;

			return $this;
		}
	}