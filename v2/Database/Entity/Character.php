<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 26-11-19
	 * Time: 23:36
	 */

	namespace v2\Database\Entity;

	use v2\Manager;

	class Character extends Entity
	{
		/**
		 * @var string
		 */
		CONST TABLE = Manager::TEST ? 'characters_2' : 'characters';

		/**
		 * @var string
		 */
		CONST IMAGE = '__img.jpg';

		/**
		 * @var integer
		 */
		protected $id;

		/**
		 * @var string
		 */
		private $name = null;

		/**
		 * @var string
		 */
		private $romanji = null;

		/**
		 * @var string
		 */
		private $age = null;

		/**
		 * @var string
		 */
		private $gender = null;

		/**
		 * @var string
		 */
		private $height = null;

		/**
		 * @var string
		 */
		private $weight = null;

		/**
		 * @var string
		 */
		private $cup = null;

		/**
		 * @var string
		 */
		private $bust = null;

		/**
		 * @var string
		 */
		private $waist = null;

		/**
		 * @var string
		 */
		private $hips = null;


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
		public function getName(): string
		{
			return $this->name;
		}

		/**
		 * @param string $name
		 * @return Character
		 */
		public function setName(string $name): Character
		{
			$this->name = $name;

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
		 * @return Character
		 */
		public function setRomanji(string $romanji): Character
		{
			$this->romanji = $romanji;

			return $this;
		}

		/**
		 * @return string
		 */
		public function getAge(): string
		{
			return $this->age;
		}

		/**
		 * @param string $age
		 * @return Character
		 */
		public function setAge(string $age): Character
		{
			$this->age = $age;

			return $this;
		}

		/**
		 * @return string
		 */
		public function getGender(): string
		{
			return $this->gender;
		}

		/**
		 * @param string $gender
		 * @return Character
		 */
		public function setGender(string $gender): Character
		{
			$this->gender = $gender;

			return $this;
		}

		/**
		 * @return string
		 */
		public function getHeight(): string
		{
			return $this->height;
		}

		/**
		 * @param string $height
		 * @return Character
		 */
		public function setHeight(string $height): Character
		{
			$this->height = $height;

			return $this;
		}

		/**
		 * @return string
		 */
		public function getWeight(): string
		{
			return $this->weight;
		}

		/**
		 * @param string $weight
		 * @return Character
		 */
		public function setWeight(string $weight): Character
		{
			$this->weight = $weight;

			return $this;
		}

		/**
		 * @return string
		 */
		public function getCup(): string
		{
			return $this->cup;
		}

		/**
		 * @param string $cup
		 * @return Character
		 */
		public function setCup(string $cup): Character
		{
			$this->cup = $cup;

			return $this;
		}

		/**
		 * @return string
		 */
		public function getBust(): string
		{
			return $this->bust;
		}

		/**
		 * @param string $bust
		 * @return Character
		 */
		public function setBust(string $bust): Character
		{
			$this->bust = $bust;

			return $this;
		}

		/**
		 * @return string
		 */
		public function getWaist(): string
		{
			return $this->waist;
		}

		/**
		 * @param string $waist
		 * @return Character
		 */
		public function setWaist(string $waist): Character
		{
			$this->waist = $waist;

			return $this;
		}

		/**
		 * @return string
		 */
		public function getHips(): string
		{
			return $this->hips;
		}

		/**
		 * @param string $hips
		 * @return Character
		 */
		public function setHips(string $hips): Character
		{
			$this->hips = $hips;

			return $this;
		}
	}
?>