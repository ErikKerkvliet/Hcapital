<?php
	namespace v2\Classes;

	use v2\Database\Entity\InvalidLink;
	use v2\Manager;
	use v2\Traits\TextHandler;

	class InvalidLinks
	{
		use TextHandler;

		private $invalidLinks = [];

		public function __construct()
		{
			$file = fopen(Manager::TEMPLATE_FOLDER . 'InvalidLinks.html', 'r');
			$this->content = fread($file, 10000);
			$this->cssFiles = [
				'Home',
				'InvalidLinks',
			];

			$this->jsFiles = [
				'InvalidLinks'
			];
		}

		public function buildContent()
		{
			$this->setInvalidLinks();

			$this->fors = [
				'invalid_links'  => $this->invalidLinks,
			];

			$this->fillFors();
		}

		private function setInvalidLinks()
		{
			$invalidLinkRepository = app('em')->getRepository(InvalidLink::class);
			$items = $invalidLinkRepository->findAll([], ['id' => 'DESC']);

			$row = 0;
			foreach($items as $item) {
				$this->invalidLinks[] = [
					'tr' => 'row-color-' . ($row % 2),
					'id' => $item->getId(),
					'entry_id' => $item->getEntry(true),
					'link_id' => $item->getLink(true),
				];
				$row++;
			}
		}
	}