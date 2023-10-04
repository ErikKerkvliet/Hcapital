<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 16-8-20
	 * Time: 14:32
	 */

	namespace v2\Classes;

	use v2\Database\Entity\Entry;
	use v2\Database\Entity\Link;
	use v2\Database\Entity\Thread;
	use v2\Database\Repository\EntryRepository;
	use v2\Database\Repository\LinkRepository;
	use v2\Database\Repository\ThreadRepository;
	use v2\Manager;

	class Threads extends TextHandler
	{
		public $page = 0;

		private $threads = [];

		private $pages = [];

		private $nameAndIds = [];

		private $type = '';

		private $author = '';

		/**
		 * Home constructor.
		 * @param $page
		 */
		public function __construct($page = 0)
		{
			$this->type = '3d';
			$this->author = 'ZoZo';

			$file = fopen(Manager::TEMPLATE_FOLDER . 'Threads.html', 'r');
			$this->content = fread($file, 10000);

			$this->cssFiles = [
				'Threads',
				'Home',
			];

			$this->jsFiles = [
				'Home',
				'Threads',
			];

			$this->page = $page;
		}

		public function buildContent()
		{
			$admin = AdminCheck::checkForAdmin();

			$this->ifs = [
				'online'   => $admin && ! AdminCheck::checkForLocal(),
				'local'    => $admin && AdminCheck::checkForLocal(),
			];
			$this->getTreadData();
			$this->getPages();
			$this->getNamAndIdsBySearch(request('link-part'));

			$this->fors = [
				'threads'       => $this->threads,
				'pagesTop'      => $this->pages,
				'pagesBottom'   => $this->pages,
				'nameAndIds'    => $this->nameAndIds,
			];

			$this->fillIfs();
			$this->fillFors();
			$this->fillPlaceHolders();
		}

		private function getTreadData() {
			/** @var ThreadRepository $threadRepository */
			$threadRepository = app('em')->getRepository(Thread::class);
			$developerRepository = app('em')->getRepository(\v2\Database\Entity\Developer::class);

			$first = $this->page * 100 - 100;
			$threads = $threadRepository->findAllByPageTypeAndAuthor([$first, 100], $this->type, $this->author);
			$row = 1;
			array_map(function($thread) use (&$row, $developerRepository) {
				$row++;
				$entry = $thread->getEntry();

				$developers = $entry ? $developerRepository->findByEntry($entry) : [];
				$developers = array_map(function ($developer) {
					return $developer->getName();
				}, $developers);

				$id = $entry ? $entry->getId() : 0;
				$cover = 'entry_images/entries/' . $id . '/cover/_cover_m.jpg';
				$target = AdminCheck::checkForLocal() ? '' : 'http://hcapital.tk';
				$target .= '/?v=2&id=' . $id;
				$this->threads[] = [
					'tr'        => 'row-color-' . ($row % 2),
					'threadId'  => $thread->getId(),
					'entryId'   => $entry ? $entry->getId() : 0,
					'author'    => $thread->getAuthor(),
					'title'     => $entry ? $entry->getTitle() : 0,
					'developers'=> implode(',', $developers),
					'released'  => $entry ? $entry->getReleased() : '',
					'url'       => $thread->getUrl(),
					'checked'   => $thread->getConfirmed() ? 'checked' : '',
					'target'    => $target,
					'cover'     => $cover,
				];
			}, $threads);
		}

		private function getPages() {
			/** @var ThreadRepository $threadRepository */
			$threadRepository = app('em')->getRepository(Thread::class);

			$threads = $threadRepository->findBy(['author' => $this->author, 'type' => $this->type]);

			$pages = (count($threads) / 100);
			$nr = 1;
			for ($i = 0; $i < $pages; $i++) {
				$this->pages[] = [
					'nr' => $nr,
				];
				$nr++;
			}
		}

		private function getNamAndIdsBySearch($search) {
			/** @var EntryRepository $entryRepository */
			$entryRepository = app('em')->getRepository(Entry::class);

			$entries = array_merge($entryRepository->findBySearch($search, 'game'),
				$entryRepository->findBySearch($search, '3d'));

			if (AdminCheck::checkForLocal()) {
				$entries = array_merge($entries, $entryRepository->findBySearch($search, 'app'));
			}

			usort($entries, function ($a, $b) {
				return $a->getRomanji() <=> $b->getRomanji();
			});

			$titles = [];
			$row = 0;

			/** @var Entry $entry */
			foreach ($entries as $entry) {
				$entryId = (int) $entry->getId();
				while (strlen($entryId) < 5) {
					$entryId = '0' . $entryId;
				}
				$entryId = 'E' . $entryId;

				$titles[] = $entry->getTitle();
				$this->nameAndIds[] = [
					'tr'        => ($row % 2),
					'type'      => $entry->getType(),
					'copyId'   => $entryId,
					'entryId'   => $entry->getId(),
					'title'     => $entry->getRomanji(),
				];
				$row++;
			}
		}

		private function getNamAndIdsByLinkPart($filename) {
			$searchText = str_replace(' ', '_', $filename);

			/** @var LinkRepository $linkRepository */
			$linkRepository = app('em')->getRepository(Link::class);

			$links = $linkRepository->findByLinkPart($searchText);

			$titles = [];
			$row = 0;
			/** @var Link $link */
			foreach ($links as $link) {
				$parts = explode('/',$link->getLink());
				$filename = end($parts);
				$title = reset(explode('.', $filename));
				$title = str_replace('_', ' ', $title);
				$entry = $link->getEntry();

				if (! in_array($title, $titles)) {
					$titles[] = $title;
					$this->nameAndIds[] = [
						'tr'        => ($row % 2),
						'type'      => $entry->getType(),
						'entryId'   => $entry->getId(),
						'title'     => $title,
					];
					$row++;
				}
			}
		}
	}