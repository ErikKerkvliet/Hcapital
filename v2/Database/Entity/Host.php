<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 26-11-19
	 * Time: 23:22
	 */

	namespace v2\Database\Entity;

	class Host extends Entity
	{
		/**
		 * @var string
		 */
		const TABLE = 'hosts';

		/**
		 * @var string
		 */
		const HOST_RAPIDGATOR = 'rapidgator';

		/**
		 * @var string
		 */
		const HOST_MEXASHARE = 'mexashare';

		/**
		 * @var string
		 */
		const HOST_KATFILE = 'katfile';

		/**
		 * @var string
		 */
		const HOST_ROSEFILE = 'rosefile';

		/**
		 * @var string
		 */
		const HOST_DDOWNLOAD = 'ddownload';

		/**
		 * @var string
		 */
		const HOST_FIKPER = 'fikper';

		/**
		 * @var string
		 */
		const HOST_BIGFILE = 'bigfile';

		/**
		 * @var array
		 */
		const HOSTS = [
			self::HOST_RAPIDGATOR,
			self::HOST_KATFILE,
			self::HOST_MEXASHARE,
			// self::HOST_DDOWNLOAD,
			// self::HOST_FIKPER,
			// self::HOST_ROSEFILE,
		];

		/**
		 * @var integer
		 */
		protected $id;

		/**
		 * @var string
		 */
		protected $name;

		/**
		 * @var string
		 */
		protected $url;

		/**
		 * @var bool
		 */
		protected $active;

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
		public function getName()
		{
			return $this->name;
		}

		/**
		 * @param string $name
		 */
		public function setName($name)
		{
			$this->name = $name;
		}

		/**
		 * @return string
		 */
		public function getUrl()
		{
			return $this->url;
		}

		/**
		 * @param string $url
		 */
		public function setUrl($url)
		{
			$this->url = $url;
		}

		/**
		 * @return bool
		 */
		public function isActive()
		{
			return $this->active;
		}

		/**
		 * @param string $bool
		 */
		public function setActive($isActive)
		{
			$this->active = $isActive;
		}	
	}
?>