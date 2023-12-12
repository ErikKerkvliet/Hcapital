<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 28-1-20
	 * Time: 21:43
	 */

	namespace v2\Classes;

	use v2\Database\Entity\Entry;
	use v2\Database\Entity\SeriesRelation;
	use v2\Database\Repository\DeveloperRepository;
	use v2\Database\Repository\EntryRepository;
	use v2\Database\Repository\SeriesRelationRepository;
	use v2\Manager;

	class Developer extends TextHandler
	{
		private $developer = null;

		/**
		 * Developer constructor.
		 * @param $developer
		 */
		public function __construct($developer)
		{
			$this->developer = $developer;

			$file = fopen(Manager::TEMPLATE_FOLDER . 'Developer.html', 'r');
			$this->content = fread($file, 100000);

			$this->cssFiles = [
				'Developer',
				'Searcher',
				'OrderBar',
				'EntryList',
			];

			$this->jsFiles = [
				'Searcher',
				'EntryList',
			];
		}

		/**
		 * Setup all the page info
		 */
		public function buildContent()
		{
			$this->fors = [
				'relations'    => $this->getDevelopers(),
			];

			$this->placeHolders = [
				'order'         => request('order'),
				'by'            => request('by'),
				'id'            => $this->developer->getId(),
				'type'          => $this->getType(),
				'listType'      => lcfirst($this->getType()[0]),
				'kanji'         => $this->developer->getKanji(),
				'romanji'       => $this->developer->getName(),
				'homepage'      => $this->developer->getHomepage(),
				'list'          => $this->getList(),
				'orderBar'      => $this->getOrderBar(),
				'hasKanji'      => $this->developer->getKanji() ? 'has-kanji' : '',
				'hasRelation'   => $this->fors['relations'] != [] ? 'has-relation' : '',
			];

			$this->ifs = [
				'relations'    => ($this->fors['relations'] != []),
				'homepage'     => $this->developer->getHomepage() ?: null,
			];

			$this->fillIfs();
			$this->fillFors();
			$this->fillPlaceHolders();
		}

		private function getList()
		{
			$limit = [0, 25];
			if ($page = request('p')) {
				$limit = [$page * 25, 25];
			}

			$orderBy = ['released', 'DESC'];
			if (request('by') && request('order')) {
				$orderBy = [request('by'), request('order')];
			}

			if ($this->developer->getType() == 'game' || $this->developer->getType() == 'app') {
				/** @var EntryRepository $entryRepository */
				$entryRepository = app('em')->getRepository(Entry::class);

				$entries = $entryRepository->findByDeveloper($this->developer, $orderBy, $limit);

				$list = new GameList($entries);
			} else {
				/** @var SeriesRelationRepository $seriesRelationRepository */
				$seriesRelationRepository = app('em')->getRepository(SeriesRelation::class);

				$series = $seriesRelationRepository->findByDeveloper($this->developer, $orderBy, $limit);

				if ($orderBy && request('by') == 'released') {
					if (request('order') == 'asc') {
						usort($series, function ($a, $b) {
							return $a->getReleased() <=> $b->getReleased();
						});
					} else {
						usort($series, function ($a, $b) {
							return $a->getReleased() <=> $b->getReleased();
						});

						$tmpDates = [];
						$tmpSeries = [];
						$stash = [];
						foreach($series as $serie) {
							if (! in_array($serie->getReleased(), $tmpDates)) {
								$tmpDates[] = $serie->getReleased();
								$tmpSeries[] = $serie;
								continue;
							}
							$stash[] = $serie;
						}
						usort($tmpSeries, function ($a, $b) {
							return $b->getReleased() <=> $a->getReleased();
						});

						$series = array_merge($tmpSeries, $stash);
					}
				}

				$list = new OvaList($series);
			}

			$list->buildContent();

			return $list->getContent();
		}

		private function getDevelopers()
		{
			/** @var DeveloperRepository $developerRepository */
			$developerRepository = app('em')->getRepository(\v2\Database\Entity\Developer::class);

			$relations = $developerRepository->findByRelation($this->developer);

			$developers = [];
			foreach ($relations as $relation) {
				$developers[] = [
					'url'       => $relation->getId(),
					'developer' => $relation->getName(),
					'comma'     => $relation === end($relations) ? '' : ', ',
				];
			}
			return $developers;
		}

		private function getOrderBar()
		{
			$orderBar = new OrderBar('game');

			$orderBar->buildContent();

			return $orderBar->getContent();
		}

		private function getType() {
			if ($this->developer->getType() == 'game') {
				return 'Games';
			}
			return "OVA's";
		}
	}