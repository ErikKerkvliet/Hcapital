<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 27-12-19
	 * Time: 21:53
	 */

	namespace v2\Database\Repository;


	use v2\Database\Entity\Download;

	class DownloadRepository extends Repository
	{
		/**
		 * Entity class of repository
		 */
		protected $entity = Download::class;
	}