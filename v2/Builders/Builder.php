<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 4-1-20
	 * Time: 12:10
	 */

	namespace v2\Builders;


	use v2\Database\Entity\Entry;

	class Builder
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