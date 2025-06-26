<?php

	namespace v2\Factories;

    use v2\Factories\FactoryAbstract;

	/**
	 * Created by VSCode.
	 * User: erik
	 * Date: 26-7-25
	 * Time: 23:13
	 */

	class BannedFactory extends FactoryAbstract
	{
        protected $entity = \v2\Database\Entity\Banned::class;

        public function __construct()
        {
            // 
        }
	}