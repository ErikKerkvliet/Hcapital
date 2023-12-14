<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 4-1-20
	 * Time: 12:10
	 */

	namespace v2\Traits;


	use v2\Database\Entity\Entry;

    trait Builder
	{
		/**
		 * @var Entry|null
		 */
		protected $entry = null;

		protected $html = '';

		public function __construct($entry)
		{
			$this->entry = $entry;
		}
	}

