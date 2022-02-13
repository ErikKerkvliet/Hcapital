<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 18-4-20
	 * Time: 19:13
	 */

	namespace v2\Database\Entity;


	class Log extends Entity
	{
		/**
		 * @var string
		 */
		CONST TABLE = 'log';

		/**
		 * @var int
		 */
		protected $id = null;

		/**
		 * @var string
		 */
		private $message = '';

		/**
		 * @return int|null
		 */
		public function getId()
		{
			return $this->id;
		}

		/**
		 * @param $message
		 * @return Log
		 */
		public function setMessage($message): Log
		{
			$this->message = $message;

			return $this;
		}

		/**
		 * @return string
		 */
		public function getMessage(): string
		{
			return $this->message;
		}
	}