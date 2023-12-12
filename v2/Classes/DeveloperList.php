<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 24-1-20
	 * Time: 15:17
	 */

	namespace v2\Classes;

	use v2\Database\Repository\DeveloperRepository;
	use v2\Manager;

	class DeveloperList extends TextHandler
	{
		private $items = [];

		/**
		 * DeveloperList constructor.
		 * @param $items
		 */
		public function __construct($items)
		{
			$this->items = $items;
			$file = fopen(Manager::TEMPLATE_FOLDER . 'DeveloperList.html', 'r');
			$this->content = fread($file, 100000);

			$this->cssFiles = [
			];

			$this->jsFiles = [
			];
		}

		/**
		 * Setup all the page info
		 */
		public function buildContent()
		{
			$this->placeHolders = [
				'developers' => $this->getList(),
				'type'       => request('t'),
			];

			$this->ifs = [
				'previous'  => true,
				'next'      => true,
			];

			$this->fillIfs();

			$this->fillPlaceHolders();
		}

		public function getList()
		{
			$items = $this->items ?: $this->getListItems();

			$developers = new Developers($items);

			$developers->buildContent();

			return $developers->getContent();
		}

		private function getListItems()
		{
			$developerRepository = new DeveloperRepository(app('em'), \v2\Database\Entity\Developer::class);
			if ($type = request('l')) {
				$items = $developerRepository->findBy(['type' => $type]);
			} else {
				$items = $developerRepository->findAll(['developer', 'ASC'], [0, 76]);
			}
			return $items;
		}
	}