<?php

	namespace v2\Factories;

	use v2\Factories\FactoryAbstract;
	
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 14-11-25
	 * Time: 12:00
	 */

	class InvalidLinkFactory extends FactoryAbstract
	{
		protected $entity = \v2\Database\Entity\InvalidLink::class;

        public function __construct()
        {
            // 
        }
	}