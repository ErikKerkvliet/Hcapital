<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 26-11-19
	 * Time: 23:22
	 */

	namespace v2\Database\Entity;

	class Link2 extends Entity
	{
		/**
		 * @var string
		 */
		CONST TABLE = 'entry_links_2';

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
		private $link;

		/**
		 * @var int|null
		 */
		private $part;

		/**
		 * @var string|null
		 */
		private $comment = '';


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
		 * @param mixed $entity
		 * @return Link2
		 * @throws /Exception
		 */
		public function setEntry($entity): Link2
		{
			$this->entry = $this->setEntity($entity);

			return $this;
		}

		/**
		 * @return string
		 */
		public function getLink():? string
		{
			return $this->link;
		}

		/**
		 * @param null|string $link
		 * @return Link2
		 */
		public function setLink(string $link): Link2
		{
			$this->link = $link;

			return $this;
		}

		/**
		 * @return int|null
		 */
		public function getPart():? int
		{
			return (int) $this->part;
		}

		/**
		 * @param int|null $part
		 * @return Link2
		 */
		public function setPart($part): Link2
		{
			$this->part = $part;

			return $this;
		}

		/**
		 * @return string|null
		 */
		public function getComment():? string
		{
			return $this->comment;
		}

		/**
		 * @param string $comment
		 * @return Link2
		 */
		public function setComment(string $comment): Link2
		{
			$this->comment = $comment;

			return $this;
		}
	}
?>