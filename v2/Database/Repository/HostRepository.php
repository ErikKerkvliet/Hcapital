<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 7-11-23
	 * Time: 21:40
	 */

	namespace v2\Database\Repository;

	use v2\Database\Entity\Host;

	class HostRepository extends Repository
	{
		/**
		 * Entity class of repository
		 */
		protected $entity = Host::class;
	}