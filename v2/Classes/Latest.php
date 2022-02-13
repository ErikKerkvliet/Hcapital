<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 10-2-20
	 * Time: 20:54
	 */

	namespace v2\Classes;

	use v2\Database\Entity\Entry;
	use v2\Database\Entity\EntryDeveloper;
	use v2\Database\Repository\EntryRepository;
	use v2\Database\Repository\Repository;
	use v2\Manager;

	class Latest extends TextHandler
	{
		private $page = 0;

		private $type = '';

		/**
		 * @var bool|string
		 */
		private $originalContent = '';

		public function __construct($page, $type)
		{
			$file = fopen(Manager::TEMPLATE_FOLDER . 'Latest.html', 'r');
			$this->content = fread($file, 10000);
			$this->originalContent = $this->content;

			$this->page = $page;
			$this->type = $type;
		}

		public function buildContent()
		{
			$this->getEntries();
//			$file = fopen(Manager::TEMPLATE_FOLDER . 'Latest.html', 'r');
//			$this->content = fread($file, 10000);
//			$this->originalContent = $this->content;
//
//			$this->placeHolders = [
//				'type'  => $this->type,
//			];
//
//			$this->fors = [
//				'entries' => $this->getEntries(),
//			];
//
//			$this->fillPlaceHolders();
//			$this->fillFors();
		}

		private function getEntries() {
			/** @var EntryRepository $entryRepository */
			$entryRepository = app('em')->getRepository(Entry::class);

			$orderBy = isset($_POST['ug']) ? ['released','desc'] : ['last_edited' => 'desc'];
			$limit = [$this->page * 10, 10];

			if (isset($_POST['ug'])) {
				$entries = $entryRepository->findUpcomingEntries($this->type, $orderBy, $limit);
			} else {
				$entries = $entryRepository->findLatestEntries($this->type, $limit);
			}

			$content = '';
			/** @var Entry $entry */
			foreach ($entries as $entry) {
				$this->content = $this->originalContent;

				$this->ifs = [
					'website'       => $entry->getWebsite() ?: false,
					'information'   => $entry->getInformation() ?: false,
					'size'          => $entry->getSize() ?: false,
				];

				$this->placeHolders = [
					'id'            => $entry->getId(),
					'title'         => $entry->getTitle(),
					'website'       => $entry->getWebsite(),
					'information'   => $entry->getInformation(),
					'released'      => $entry->getReleased(),
					'size'          => $entry->getSize(),
					'developers'    => $this->getDevelopers($entry),
					'site'          => $this->getSiteType($entry->getInformation()),
					'cover'         => $this->getCover($entry),
				];

				$this->fillFors();
				$this->fillIfs();
				$this->fillPlaceHolders();

				$content .= $this->content;
			}

			$this->content = $content;

//			/** @var Entry $entry */
//			foreach($entities as $entry) {
//				$entries[] = [
//					'id'            => $entry->getId(),
//					'title'         => $entry->getTitle(),
//					'released'      => $entry->getReleased(),
//					'website'       => $entry->getWebsite() ?: false,
//					'information'   => $entry->getInformation() ?: false,
//					'developers'    => $this->getDevelopers($entry),
//					'site'          => $this->getSiteType($entry->getInformation()),
//					'cover'         => $this->getCover($entry),
//				];
//			}
//			return $entries;
		}

		/**
		 * @return string
		 */
		private function getCover($entry): string
		{
			return isset($_SESSION['_18']) && $_SESSION['_18'] == '-' ? 'images/No Imagem.jpg' :
				getImages($entry, 'cover', 'm');
		}

		/**
		 * @return string
		 */
		private function getSiteType($site): string
		{
			$site = strtolower($site);
			if (strpos($site, 'getchu') !== false) {
				return 'Getchu';
			};
			if (strpos($site, 'dlsite') !== false) {
				return 'DLsite';
			};
			return 'Information';
		}

		private function getDevelopers($entry): string
		{
			$entryDeveloperRepository = app('em')->getRepository(EntryDeveloper::class);
			$entities = $entryDeveloperRepository->findBy(['entry' => $entry]);

			$developers = [];

			foreach ($entities as $entity) {
				$developer = $entity->getDeveloper();
				$developers[] = '<a href="?v=2&did=' . $developer->getId() . '">' . $developer->getName() . '</a>';
			}

			return implode(' & ', $developers);
		}
	}