<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 26-11-19
	 * Time: 23:22
	 */

	namespace v2\Database\Entity;

	class Link extends Entity
	{
		/**
		 * @var string
		 */
		const TABLE = 'entry_links';

		/** @var string  */
		const BANNED_RAPIDGATOR_URL = 'https://rapidgator.net/file/69c900ee31cd065086ab9adba656588c/E00105.rar.html';

		/** @var string  */
		const BANNED_MEXASHARE_URL = 'https://mexa.sh/2hyq4vb11r8o/G2504248.rar.html';

		/** @var string  */
		const BANNED_KATFILE_URL = 'https://katfile.com/k9t3sm6mnrpy/G2504248.rar.html';

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
		 * @var string
		 */
		private $created_at;


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
		 * @return Link
		 * @throws /Exception
		 */
		public function setEntry($entity): Link
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
		 * @return Link
		 */
		public function setLink(string $link): Link
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
		 * @return Link
		 */
		public function setPart($part): Link
		{
			$this->part = $part;

			return $this;
		}

		/**
		 * @return string|null
		 */
		public function getComment(): ?string
		{
			return $this->comment;
		}

		/**
		 * @param string $comment
		 * @return Link
		 */
		public function setComment(string $comment): Link
		{
			$this->comment = $comment;

			return $this;
		}

		public function getCreated(): ?string
		{
			return $this->created_at;
		}

		public function setCreated(string $created): Link
		{
			$this->created_at = $created;

			return $this;
		}
	}
?>