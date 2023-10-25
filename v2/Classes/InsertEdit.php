<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 23-2-20
	 * Time: 21:12
	 */

	namespace v2\Classes;

	use v2\Database\Entity\DeveloperRelation;
	use v2\Database\Entity\Entry;
	use v2\Database\Entity\Developer;
	use v2\Database\Entity\EntryDeveloper;
	use v2\Database\Entity\EntryRelation;
	use v2\Database\Entity\Link;
	use v2\Database\Repository\DeveloperRepository;
	use v2\Database\Repository\EntryDeveloperRepository;
	use v2\Database\Repository\EntryRelationRepository;
	use v2\Database\Repository\LinkRepository;
	use v2\Manager;

	class InsertEdit extends TextHandler
	{
		/**
		 * @var Entry|null
		 */
		private $entry = null;

		private $types = [];

		private $timeTypes = [];

		private $type = false;

		private $timeType = false;

		private $developers = [];

		private $relations = [];

		private $links = [];

		private $images = [];

		private $insert = false;

		public function __construct($entry, $insert = false)
		{
			$this->entry = $entry;
			$this->insert = $insert;
			$file = fopen(Manager::TEMPLATE_FOLDER . 'InsertEdit.html', 'r');
			$this->content = fread($file, 100000);

			$this->cssFiles = [
				'InsertEdit',
			];

			$this->jsFiles = [
				'AddComponent',
				'EntryAction',
			];

			$this->type = $this->entry ? $this->entry->getType() : 'ova';
			$this->timeType = $this->entry ? $this->entry->getTimeType() : 'new';

			$this->types();
			$this->timeTypes();

			if (! $this->insert) {
				$this->developers();
				$this->relations();
				$this->links();
				$this->images();
			}
		}

		/**
		 * Setup all the page info
		 */
		public function buildContent()
		{
			if (! $this->insert) {
				$this->placeHolders = [
					'id' => $this->entry->getId(),
					'action' => 'edit&id=' . $this->entry->getId(),
					'cover' => '',
					'title' => $this->entry->getTitle(),
					'romanji' => $this->entry->getRomanji(),
					'released' => $this->entry->getReleased(),
					'size' => $this->entry->getSize(),
					'website' => $this->entry->getWebsite(),
					'information' => $this->entry->getInformation(),
					'password' => $this->entry->getPassword(),
					'rapidgator' => isset($this->links['Rapidgator'])
						? implode('splitter', $this->links['Rapidgator']['Rapidgator'])
					: '',
					'mexashare' => isset($this->links['Mexashare'])
						? implode('splitter', $this->links['Mexashare']['Mexashare'])
						: '',
				];
			} else {
				$this->placeHolders = [
					'id' => 0,
					'action' => 'insert',
					'cover' => '_cover_.jpg',
					'title' => '',
					'romanji' => '',
					'released' => '',
					'size' => '',
					'website' => '',
					'information' => '',
					'password' => '',
					'rapidgator' => '',
					'mexashare' => '',
				];
			}
			unset($this->links['Rapidgator']);
			unset($this->links['Mexashare']);

			$this->ifs = [
				'insertEdit' => true,
				'getPost' => false,
				'local' => AdminCheck::checkForLocal(),
			];

			$this->fors = [
				'developerSelect'   => $this->getDeveloperSelect(),
				'types'             => $this->types,
				'time_types'        => $this->timeTypes,
				'developers'        => $this->developers,
				'relations'         => $this->relations,
				'otherLinks'        => $this->getLinksForView(),
				'images'            => $this->images,
			];

			$this->fillIfs();
			$this->fillFors();
			$this->fillPlaceHolders();
		}

		private function images()
		{
			$imageFolderPath = getcwd() . '/entry_images/entries/' . $this->entry->getId() . '/cg/';
			$files = scandir($imageFolderPath) ?: [];

			foreach ($files as $key => $image) {
				if (! is_dir($image)) {
					$this->images[] = [
						'nr'    => $key - 2,
						'image' => $image,
					];
				}
			}
		}

		private function types()
		{
			if ($this->insert) {
				$this->types[] = ['selected' => false, 'value' => 'game', 'content' => 'Game'];
				$this->types[] = ['selected' => false, 'value' => 'ova', 'content' => 'OVA'];
				$this->types[] = ['selected' => false, 'value' => '3d', 'content' => '3D'];
				$this->types[] = ['selected' => false, 'value' => 'app', 'content' => 'App'];
				return;
			}
			$this->types[] = [
				'selected' => $this->getSelectedType('game'),
				'value' => 'game',
				'content' => 'Game',
			];
			$this->types[] = [
				'selected' => $this->getSelectedType('ova'),
				'value' => 'ova',
				'content' => 'OVA',
			];
			$this->types[] = [
				'selected' => $this->getSelectedType('3d'),
				'value' => '3d',
				'content' => '3D',
			];
			$this->types[] = [
				'selected' => $this->getSelectedType('app'),
				'value' => 'app',
				'content' => 'App',
			];
		}

		private function timeTypes()
		{
			if ($this->insert) {
				$this->timeTypes[] = ['selected' => false, 'value' => 'new','content' => 'New'];
				$this->timeTypes[] = ['selected' => false, 'value' => 'old','content' => 'Old'];
				$this->timeTypes[] = ['selected' => false, 'value' => 'upc','content' => 'Upcoming'];
				$this->timeTypes[] = ['selected' => false, 'value' => 'inv','content' => 'Hide'];
				return;
			}
			$this->timeTypes[] = [
				'selected' => $this->getSelectedTime('new'),
				'value' => 'new',
				'content' => 'New',
			];
			$this->timeTypes[] = [
				'selected' => $this->getSelectedTime('old'),
				'value' => 'old',
				'content' => 'Old',
			];
			$this->timeTypes[] = [
				'selected' => $this->getSelectedTime('upc'),
				'value' => 'upc',
				'content' => 'Upcoming',
			];
			$this->timeTypes[] = [
				'selected' => $this->getSelectedTime('inv'),
				'value' => 'inv',
				'content' => 'Hide',
			];
		}

		private function developers()
		{
			/** @var EntryDeveloperRepository $entryDeveloperRepository */
			$entryDeveloperRepository = app('em')->getRepository(EntryDeveloper::class);
			$entryDevelopers = $entryDeveloperRepository->findBy(['entry' => $this->entry]);

			$ids = array_map(function ($entryDeveloper) {
				return $entryDeveloper->getDeveloper(true);
			}, $entryDevelopers);

			/** @var DeveloperRepository $developerRepository */
			$developerRepository = app('em')->getRepository(Developer::class);
			$developers = $developerRepository->findById($ids);

			foreach ($developers as $key => $developer) {
				$this->developers[] = [
					'id'    => $developer->getId(),
					'nr'    => $key + 1,
					'name'  => $developer->getName(),
				];
			}
		}

		private function getDeveloperSelect()
		{
			$developerRepository = app('em')->getRepository(Developer::class);
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

		private function relations()
		{
			/** @var EntryRelationRepository $entryRelationRepository */
			$entryRelationRepository = app('em')->getRepository(EntryRelation::class);
			$entryRelations = $entryRelationRepository->findEntryByRelatedEntry($this->entry);

			$relations = array_map(function ($entryRelation) {
				return $entryRelation->getEntry();
			}, $entryRelations);

			$relations = array_filter($relations, function ($relation) {
				if ($relation->getId() == $this->entry->getId()) {
					return 0;
				}
				return 1;
			});

			foreach ($relations as $key => $relation) {
				$episode = substr($relation->getTitle(), -7);

				$this->relations[] = [
					'nr'    => $key + 1,
					'id'    => $relation->getId(),
					'title' => $relation->getRomanji() . ' ' . $episode,
				];
			}
		}

		private function links()
		{
			/** @var LinkRepository $linkRepository */
			$linkRepository = app('em')->getRepository(Link::class);
			$links = $linkRepository->findBy(['entry' => $this->entry->getId()]);

			array_map(function ($link) {
				if ($link->getLink() == '-') {
					return;
				}

				$host = substr($link->getComment(), 0, 2) != '<<' ? $this->getHost($link->getLink()) : 'Rapidgator';

				$comment = $link->getComment() ?: $host;
				$link = $link->getLink();

				$this->links[$comment][$host][] = $link;

			}, $links);
		}

		private function getLinksForView()
		{
			$nr = 1;
			return array_map(function ($key, $links) use (&$nr) {
				$arr = [
					'nr'        => $nr,
					'nrUp'    => ($nr * 2),
					'nrUpUp'  => ($nr * 2) + 1,
					'comment'   => $key,
					'rapidgatorLinks' => implode('splitter', $links['Rapidgator']),
					'mexashareLinks' => implode('splitter', $links['Mexashare']),
				];
				$nr++;
				return $arr;
			}, array_keys($this->links), $this->links);
		}

		private function getSelectedType($type)
		{
			return $type == $this->type ? 'selected' : '';
		}

		private function getSelectedTime($timeType)
		{
			return $timeType == $this->timeType ? 'selected' : '';
		}

		/**
		 * @param string $link
		 * @return string
		 */
		private function getHost($link)
		{
			if ((strpos($link, 'rapidgator.net') !== false) ||
				(strpos($link, 'rg.to/') !== false)) {
				$host = 'Rapidgator';
			} else if (strpos($link, 'mexashare.com') !== false) {
				$host = 'Mexashare';
			} else if (strpos($link, 'mx-sh.net') !== false) {
				$host = 'Mexashare';
			} else if (strpos($link, 'mexa.sh') !== false) {
				$host = 'Mexashare';
			} else if (strpos($link, 'bigfile.to') !== false) {
				$host = 'Bigfile';
			} else if (strpos($link, 'katfile.com') !== false) {
				$host = 'Katfile';
			} else {
				$host = 'Link';
			}

			return $host;
		}
	}