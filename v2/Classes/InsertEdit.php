<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 23-2-20
	 * Time: 21:12
	 */

	namespace v2\Classes;

	use AddLink;
    use HostResolver;
    use v2\Database\Entity\Developer;
    use v2\Database\Entity\Entry;
    use v2\Database\Entity\EntryDeveloper;
    use v2\Database\Entity\EntryRelation;
    use v2\Database\Entity\Host;
    use v2\Database\Entity\Link;
    use v2\Database\Repository\DeveloperRepository;
    use v2\Database\Repository\EntryDeveloperRepository;
    use v2\Database\Repository\EntryRelationRepository;
    use v2\Database\Repository\LinkRepository;
    use v2\Manager;
    use v2\Traits\TextHandler;

    class InsertEdit
	{
        use TextHandler;

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

		private $hosts = [];

		private $extraHosts = [];

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
					'vndb' => $this->entry->getVndb(),
					'fullVndb' => $this->entry->getVndb() ? 'https://vndb.org/v' . $this->entry->getVndb() : '',
					'password' => $this->entry->getPassword(),
					'rapidgator' => isset($this->links['Rapidgator'])
						? implode('splitter', $this->links['Rapidgator']['Rapidgator'])
						: '',
					'mexashare' => isset($this->links['Mexashare'])
						? implode('splitter', $this->links['Mexashare']['Mexashare'])
						: '',
					'katfile' => isset($this->links['Katfile']) ?
						implode('splitter', $this->links['Katfile']['Katfile'])
						: '',
					'extraLinks' => $this->getExtraLinks(),
				];
				foreach (Host::HOSTS as $host) {
					$this->placeHolders[$host] = isset($this->links[$host])
						? implode('splitter', $this->links[$host][$host]) : '';
				}
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
                    'fullVndb' => '',
					'password' => '',
					'rapidgator' => '',
					'mexashare' => '',
					'katfile' => '',
					'extraLinks' => '',
				];
			}

			$this->setHosts();

			$this->ifs = [
				'insertEdit' => true,
				'getPost' => false,
				'local' => AdminCheck::checkForLocal(),
                'isUpdate' => ! $this->insert,
			];

			$this->fors = [
				'developerSelect'   => $this->getDeveloperSelect(),
				'types'             => $this->types,
				'time_types'        => $this->timeTypes,
				'developers'        => $this->developers,
				'relations'         => $this->relations,
				'images'            => $this->images,
				'extraHosts'        => $this->extraHosts,
				'hosts'             => $this->hosts,
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

            if (! $entryDevelopers) {
                return [];
            }
            
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

			$hostResolver = new HostResolver();
			foreach ($links as $link) {
				if ($link->getLink() == '-') {
					continue;
				}

				$host = substr($link->getComment(), 0, 2) != '<<' ?
					$hostResolver->byUrl($link->getLink()) : Host::HOST_RAPIDGATOR;

				$comment = $link->getComment() ?: '';
				$link = $link->getLink();

				$this->links[$comment][$host][] = $link;
			}
		}

		private function getExtraLinks()
		{
			if (! count($this->links)) {
				return '';
			}
			$tempLinks = $this->links;

			unset($tempLinks['']);

			$html = '';
			$nr = 1;
			foreach ($tempLinks as $key => $links) {
				$component = new AddLink($nr, [$key => $links]);
				$component->buildContent();
				$html .= $component->getContent();
				$nr++;
			}
			return $html;
		}

		private function setHosts()
		{
			$nr = 0;
			foreach (Host::HOSTS as $host) {
				$this->hosts[] = [
					'nr' => $nr,
					'label' => ucfirst($host),
					'host' => $host,
					'links' => isset($this->links[''][$host])
						? implode('splitter', $this->links[''][$host]) : '',
				];
				$nr++;
			}
		}

		private function getSelectedType($type)
		{
			return $type == $this->type ? 'selected' : '';
		}

		private function getSelectedTime($timeType)
		{
			return $timeType == $this->timeType ? 'selected' : '';
		}
	}