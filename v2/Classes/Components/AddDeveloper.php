<?php

use v2\Traits\TextHandler;

/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 28-2-20
	 * Time: 19:53
	 */

	class AddDeveloper
	{
        use TextHandler;

		/**
		 * @var int
		 */
		private $nr;

		private  $type;

		public function __construct($nr, $type = 'game')
		{
			$this->nr = $nr;
			$this->type = $type;

			$file = fopen(\v2\Manager::COMPONENT_FOLDER . 'AddDeveloper.html', 'r');
			$this->content = fread($file, 100000);
		}

		public function buildContent()
		{
			$this->placeHolders = [
				'nr'    => $this->nr,
			];

			$this->fors = [
				'developerSelect'   => $this->getDeveloperSelect(),
			];

			$this->fillFors();
			$this->fillPlaceHolders();
		}

		private function getDeveloperSelect()
		{
			/** @var \v2\Database\Repository\DeveloperRepository $developerRepository */
			$developerRepository = app('em')->getRepository(\v2\Database\Entity\Developer::class);
			$developers = $developerRepository->findBy(['type' => $this->type], ['name' => 'ASC']);

			$array = array_map(function ($developer) {
				return [
					'id'    => $developer->getId(),
					'name'  => $developer->getName(),
				];
			}, $developers);

			array_unshift($array, ['id' => 0, 'name'  => '-- developer --']);

			return $array;
		}
	}