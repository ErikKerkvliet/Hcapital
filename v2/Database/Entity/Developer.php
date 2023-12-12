<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 26-11-19
	 * Time: 23:15
	 */

	namespace v2\Database\Entity;

	use v2\Manager;

	class Developer extends Entity
	{
		/**
		 * @var string
		 */
		CONST TABLE = Manager::TEST ? 'developers_2' : 'developers';

		/**
		 * @var int
		 */
		protected $id = null;

		/**
		 * @var null|string
		 */
		private $name = null;

		/**
		 * @var null|string
		 */
		private $kanji = null;

		/**
		 * @var null|string
		 */
		private $homepage = null;

		/**
		 * @var null|string
		 */
		private $type = null;


		/**
		 * @return int|null
		 */
		public function getId()
		{
			return $this->id;
		}

		/**
		 * @return null|string
		 */
		public function getName(): ?string
		{
			if (substr($this->name, -1) == '.') {
				$this->name = substr($this->name, 0, -1);
			}
			return $this->name;
		}

		/**
		 * @param null|string $Name
		 * @return Developer
		 */
		public function setName(?string $Name): Developer
		{
			$this->name = $Name;

			return $this;
		}

		/**
		 * @return null|string
		 */
		public function getKanji(): ?string
		{
			return $this->kanji;
		}

		/**
		 * @param null|string $kanji
		 * @return Developer
		 */
		public function setKanji(?string $kanji): Developer
		{
			$this->kanji = $kanji;

			return $this;
		}

		/**
		 * @return null|string
		 */
		public function getHomepage(): ?string
		{
			return $this->homepage;
		}

		/**
		 * @param null|string $homepage
		 * @return Developer
		 */
		public function setHomepage(?string $homepage): Developer
		{
			$this->homepage = $homepage;

			return $this;
		}

		/**
		 * @return null|string
		 */
		public function getType(): ?string
		{
			return $this->type;
		}

		/**
		 * @param null|string $type
		 * @return Developer
		 */
		public function setType(?string $type): Developer
		{
			$this->type = $type;

			return $this;
		}
	}
