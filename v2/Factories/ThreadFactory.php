<?php

	namespace v2\Factories;
	
	use v2\Factories\FactoryAbstract;

	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 20-8-20
	 * Time: 20:40
	 */

	class ThreadFactory extends FactoryAbstract
	{
		protected $entity = \v2\Database\Entity\Thread::class;

        public function __construct()
        {
            // 
        }
	}
