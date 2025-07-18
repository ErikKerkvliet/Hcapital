<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 19-2-20
	 * Time: 21:23
	 */

	namespace v2\Classes;

	use v2\Database\Entity\EntryRelation;
    use v2\Database\Repository\EntryRelationRepository;
    use v2\Manager;
    use v2\Traits\TextHandler;

    class Relations
	{
        use TextHandler;

		/**
		 * @var array
		 */
		private $entries = [];

		/**
		 * @var array|int|mixed
		 */
		private $relations = [];

		/**
		 * @var array|string
		 */
		private $relationType = [];


		public function __construct($entry)
		{
			/** @var EntryRelationRepository $relationRepository */
			$relationRepository = app('em')->getRepository(EntryRelation::class);
			$this->relations = $relationRepository->findEntryByRelatedEntry($entry);

			if ($entry->getType() == 'ova' && count($this->relations) < 2) {
				return;
			}

			if ($this->relations) {
				$file = fopen(Manager::TEMPLATE_FOLDER . 'Relations.html', 'r');
				$this->content = fread($file, 10000);

				$this->relationType = $this->relations && $this->relations[0]->getType() == 'series' ? 'series' : '';
			}
		}

		/**
		 * Setup all the page info
		 */
		public function buildContent()
		{
			$this->getRelations();

			$this->fors = [
				'relations' => $this->entries,
			];
			$this->ifs = [
				'series' => $this->relationType == 'series' ? true : false,
				'notSeries' => $this->relationType == 'series' ? false : true,
			];

			$this->fillIfs();
			$this->fillFors();
		}

		private function getRelations()
		{
			foreach ($this->relations as $relation) {
				$entry = $relation->getEntry();

				$this->entries[] = [
					'id'        => $entry->getId(),
					'cover'     => $this->getCover($entry),
					'type'      => ucfirst($relation->getType()),
					'title'     => $entry->getTitle(),
					'released'  => $entry->getReleased(),
				];
			}
		}

		/**
		 * @param $entry
		 * @return string
		 */
		private function getCover($entry): string
		{
			return isset($_SESSION['_18']) && $_SESSION['_18'] == '-' ? 'images/No Imagem.jpg' :
				getImages($entry, 'cover', 'm');
		}
	}
