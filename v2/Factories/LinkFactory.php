<?php

	namespace v2\Factories;

	use v2\Factories\FactoryAbstract;
	
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 28-8-20
	 * Time: 21:14
	 */

	class LinkFactory extends FactoryAbstract
	{
		protected $entity = \v2\Database\Entity\Link::class;

        public function __construct()
        {
            // 
        }
	}