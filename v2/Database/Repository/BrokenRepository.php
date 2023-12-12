<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 1-12-19
	 * Time: 14:32
	 */

	namespace v2\Database\Repository;

	use v2\Database\Entity\Broken;

	class BrokenRepository extends Repository
	{
		/**
		 * Entity class of repository
		 */
		protected $entity = Broken::class;
	}