<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 27-12-19
	 * Time: 21:55
	 */

	namespace v2\Database\Repository;


	use v2\Database\Entity\ToDo;

	class ToDoRepository extends Repository
	{
		/**
		 * Entity class of repository
		 */
		protected $entity = ToDo::class;
	}