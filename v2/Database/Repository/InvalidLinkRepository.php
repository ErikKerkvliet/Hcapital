<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 14-11-25
	 * Time: 12:00
	 */

	namespace v2\Database\Repository;

	use v2\Database\Entity\InvalidLink;

	class InvalidLinkRepository extends Repository
	{
		/**
		 * Entity class of repository
		 */
		protected $entity = InvalidLink::class;
	}