<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 15-1-20
	 * Time: 21:15
	 */

	namespace v2\Classes;

	use LinkFactory;
	use ThreadFactory;
	use v2\Database\Entity\Character;
	use v2\Database\Entity\Developer;
	use v2\Database\Entity\Entry;
	use v2\Database\Entity\Link;
	use v2\Database\Entity\Link2;
	use v2\Database\Entity\SeriesRelation;
	use v2\Database\Entity\SharingThread;
	use v2\Database\Entity\Thread;
	use v2\Database\Repository\CharacterRepository;
	use v2\Database\Repository\DeveloperRepository;
	use v2\Database\Repository\EntryRepository;
	use v2\Database\Repository\SeriesRelationRepository;
	use v2\Database\Repository\SharingThreadRepository;
	use v2\Database\Repository\ThreadRepository;
	use v2\Manager;

	class ListMain extends TextHandler
	{

		private $type = 'all';

		private $search = '';

		private $items = [];

		private $itemTypes = [];

		private $page = 0;

		private $developerType;

		public function __construct()
		{
			$this->search = validateForQueryUse(request('s'));
			$this->type = $this->getType(request('l'));
			$this->developerType = request('t') ? $this->getType(request('t')) : null;
			$this->page = request('p') ?: 0;

			$this->cssFiles = [
				'EntryList',
				'Searcher',
				'OrderBar',
				'ListSelect',
			];
			$this->cssFiles[] = request('l') == 'd' ? 'DeveloperList' : '';

			$this->jsFiles = [
				'Searcher',
				'EntryList',
			];

			$this->jsFiles[] = $this->type == 'developer' ? 'DeveloperList' : 'EntryList';
		}

		public function buildContent()
		{
			$file = fopen(Manager::TEMPLATE_FOLDER . 'ListMain.html', 'r');
			$this->content = fread($file, 100000);

			$this->getListItems();

			$char = request('c') ?: '';
 			$by = request('by') ?: ($this->type[0] == 'c' ? 'title' : 'name');
 			$order = request('order') ?: 'desc';
 			$page = request('p') ?: 1;

			$this->ifs = [
				'searchType' => $this->type == 'all' ? false : true,
				'developer'  => $this->type[0] !== 'd' ? true : false,
				'previous'   => $this->page == 0 ? false : true,
				'next'       => (request('l') == 'd' && count($this->items) < 75) ||
									(request('l') == 'd' && count($this->items) < 35) ? false : true,
			];

 			if ($this->type != 'all') {
			    $this->placeHolders = [
			    	'navigation-top' => $this->type[0] !== 'd' ? '' : 'navigation-top',
				    'listType' => $this->type[0],
				    'search' => $this->search,
				    'char' => $char,
				    'by' => $by,
				    'order' => $order,
				    'page' => $page,
				    'searcher' => $this->getSearcher(),
				    'type' => $this->getTypeString(),
				    'orderBar' => $this->getOrderBar(),
				    'list' => $this->getList(),
			    ];
		    } else {
			    $this->content = $this->getList();
		    }

			$this->fillFors();
			$this->fillIfs();
			$this->fillPlaceHolders();
		}

		/**
		 * @param $type
		 * @return string
		 */
		public function getType($type): string
		{
			if ($type == 'g') {
				return 'game';
			}
			if ($type == 'o') {
				return 'ova';
			}
			if ($type == '3') {
				return '3d';
			}
			if ($type == 'c') {
				return 'character';
			}
			if ($type == 'd') {
				return 'developer';
			}
			return 'all';
		}

		/**
		 * @return string
		 */
		public function getTypeString(): string
		{
			if ($this->type == 'game') {
				return 'Games';
			}
			if ($this->type == 'ova') {
				return 'OVA\'s';
			}
			if ($this->type == '3d') {
				return '3D';
			}
			if ($this->type == 'character') {
				return 'Characters';
			}
			if ($this->type == 'developer') {
				return 'Developers / Producers';
			}
			return '';
		}

		private function getSearcher()
		{
			$searcher = new Searcher($this->type);

			$searcher->buildContent();

			return $searcher->getContent();
		}

		private function getOrderBar()
		{
			$orderBar = new OrderBar($this->type);

			$orderBar->buildContent();

			return $orderBar->getContent();
		}

		public function getList()
		{
			if (count($this->itemTypes) === 1) {
				if ($this->itemTypes[0] == 'game' || $this->itemTypes[0] == 'ova') {
					$this->type = $this->itemTypes[0];
					$this->getListItems();
					if (count($this->items) == 1) {
						header('Location: ?v=2&id=' . $this->items[0]->getId());
					} else {
						$type = substr($this->itemTypes[0], 0, 1);
						header('Location: ?v=2&l=' . $type . '&s=' . $this->search);
					}
				} else {
					$type = substr($this->itemTypes[0], 0, 1);
					header('Location: ?v=2&l=' . $type . '&s=' . $this->search);
				}
			}

			if ($this->type == 'all') {
				$this->content = '';

				$list = new ListSelect($this->itemTypes);
			} else {
				$classType = $this->type == '3d' ? 'ova' : $this->type;
				$className = 'v2\Classes\\' . ucfirst($classType) . 'List';

				$list = new $className($this->items);
			}
			$list->buildContent();

			return $list->getContent();
		}

		private function getListItems()
		{
			if ($this->type == 'all') {
				/** @var EntryRepository $entryRepository */
				$entryRepository = app('em')->getRepository(Entry::class);

				if (false && adminCheck::checkForAdmin()) {
					$sharingRepository = app('em')->getRepository(SharingThread::class);
					$entryRepository = app('em')->getRepository(Entry::class);

					$sharing = $sharingRepository->findBy(['type' => 'ova']);
					$entries = $entryRepository->findBy(['type' => '3d']);

					$titles = array_filter(array_map(function($entry) {
						return [
							'id'    => $entry->getId(),
							'title' => $entry->getTitle(),
						];
					}, $entries));

					$threads = array_filter(array_map(function($thread) {
						return [
							'title' => $thread->getTitle(),
							'link'  => $thread->getUrl(),
							'author'=> $thread->getAuthor(),
						];
					}, $sharing));

					$matches = [];
					foreach($threads as $thread) {
						foreach ($titles as $title) {
							if (strpos($thread['title'], $title['title']) !== false) {
								$matches[] = [
									'entry'     => $title['id'],
									'author'    => $thread['author'],
									'type'      => '3d',
									'url'       => $thread['link'],
								];
							}
						}
					}

					$factory = new ThreadFactory();
					foreach ($matches as $match) {
						$factory->create($match);
					}
					app('em')->flush();

				} else {
					$types = $entryRepository->findAllTypes($this->search);
				}

				return $this->itemTypes = $types;
			}
			/**
		    * @var EntryRepository|DeveloperRepository|CharacterRepository|SeriesRelationRepository $entityRepository
			*/
			$entityRepository = ($this->type == 'game') ?
				app('em')->getRepository(Entry::class) : ($this->type == 'developer' ?
					app('em')->getRepository(Developer::class) : ($this->type == 'character' ?
						app('em')->getRepository(Character::class) :
							app('em')->getRepository(SeriesRelation::class)));


			$orderBy = $this->type == 'ova' || $this->type == 'game' || $this->type == '3d' ?
				['released', 'DESC'] : ($this->type == 'character' ? ['name', 'ASC'] : ['name', 'ASC']);

			if (request('by') && request('order')) {
				$orderBy = [request('by'), request('order')];
			}


			$limit = $this->type == 'developer' ? [($this->page * 75), 75] : [($this->page * 35), 35];

			if ($this->developerType) {
				return $this->items = $entityRepository
					->findByType($this->developerType, $orderBy, $limit, request('c'));
			}

			if ($developer = request('developer')) {
				return $this->items = $entityRepository->findAll((int) $developer, $orderBy, $limit);
			}

			if (($character = request('character'))) {
				return $this->items = $entityRepository->findByCharacter((int) $character, $orderBy, $limit);
			}

			if ($char = request('c')) {
				if ($char == 'all' && (request('l') == 'o' || request('l') == '3')) {
					return $this->items = $entityRepository->findByChar($char, $orderBy, $limit, $this->type);
				}
				if (request('c') == 'd') {
					$limit = [($this->page * 75), 75];
				}
				return $this->items = $char == 'all' ?
					$entityRepository->findAll(['id', 'desc'], $limit) :
						$entityRepository->findByChar($char, $orderBy, $limit, $this->type);
			}

			$this->items = ($this->type == 'developer') ?
				$entityRepository->findBySearch($this->search, $orderBy, $limit) :
					($this->type == 'character' ?
						$entityRepository->findBySearch($this->search, $orderBy, $limit) :
							$entityRepository->findBySearch($this->search, $this->type, $orderBy, $limit));

			return true;
		}
	}