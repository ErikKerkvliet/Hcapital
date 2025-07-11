<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 10-2-20
	 * Time: 20:54
	 */

	namespace v2\Classes;

	use v2\Database\Entity\Entry;
	use v2\Database\Entity\Link;
    use v2\Manager;
    use v2\Traits\TextHandler;

    class LastAdded
	{
        use TextHandler;

		private $page = 0;

		private $type;

		public function __construct($page, $type)
		{
			$this->page = $page;
			$this->type = $type;
		}

		public function buildContent()
		{
			$file = fopen(Manager::TEMPLATE_FOLDER . 'LastAdded.html', 'r');
			$this->content = fread($file, 10000);

			$this->placeHolders = [
				'type'  => $this->type,
			];

			$this->fors = [
				'entries'   => $this->getEntries(),
			];

			$this->fillPlaceHolders();
			$this->fillFors();
		}

		private function getEntries() {
			$entryRepository = app('em')->getRepository(Entry::class);

			$search = ['type' => $this->type, 'time_type' => 'old'];
			$orderBy = ['last_edited' => 'desc'];
			$limit = [$this->page * 5, 5];

			// if (AdminCheck::checkForAdmin()) {
			// 	$entities = $entryRepository->findLastAddedByType($this->type, $this->page);
			// } else {
				$entities = $entryRepository->findBy($search, $orderBy, $limit);
			// }
			$entries = [];
			foreach($entities as $entry) {
				$entries[] = [
					'id'        => $entry->getId(),
					'title'     => $entry->getTitle(),
					'released'  => $entry->getReleased(),
					'cover'     => $this->getCover($entry),
				];
			}
			return $entries;
		}

		/**
		 * @return string
		 */
		private function getCover($entry): string
		{
			return isset($_SESSION['_18']) && $_SESSION['_18'] == '-' ? 'images/No Imagem.jpg' :
				getImages($entry, 'cover', 'm');
		}
	}