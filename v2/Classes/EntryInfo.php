<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 8-12-19
	 * Time: 21:06
	 */

	namespace v2\Classes;

	use EntryNameResolver;
	use v2\Builders\Characters;
	use v2\Builders\Images;
	use v2\Builders\Links2;
	use v2\Database\Entity\Entry;
	use v2\Database\Entity\EntryDeveloper;

	use v2\Database\Entity\Thread;
	use v2\Manager;

	class EntryInfo extends TextHandler
	{
		/**
		 * @var Entry|null
		 */
		protected $entry = null;

		private $title = '';

		private $single = false;

		/**
		 * EntryInfo constructor.
		 * @param $entry
		 * @param array $limit
		 */
		public function __construct($entry) {
			$this->entry = $entry;

			$this->cssFiles = [
				'EntryInfo',
				'Character',
//				'info',
			];

			$this->jsFiles = [
				'EntryInfo',
//				'Link',
			];

			if (AdminCheck::checkForLocal() && AdminCheck::checkForAdmin()) {
				$this->jsFiles[] = 'AddComponent';
			}
		}

		/**
		 * Setup all the page info
		 */
		public function buildContent()
		{
			$file = fopen(Manager::TEMPLATE_FOLDER . 'EntryInfo.html', 'r');
			$this->content = fread($file, 100000);

			if (! $this->entry) {
				$this->content = '';
				return;
			}

			$entryNameResolver = new EntryNameResolver();

			$relationsHtml = $this->getRelations();

			$title = $entryNameResolver->resolveByEntry($this->entry, $relationsHtml);

			$developerType = $this->entry->getType() == 'game' ? 'Developer' : 'Producer';

			$this->fors = [
				'developers'    => $this->getDevelopers(),
				'sharing_urls'  => $this->getSharingUrls(),
			];

			$this->ifs = [
				'romanji'       => $this->getRomanji(),
				'website'       => $this->getWebsite(),
				'information'   => $this->getInformation(),
				'size'          => $this->getSize(),
				'password'      => $this->getPassword(),
				'online'        => AdminCheck::checkForLocal() && AdminCheck::checkForAdmin(),
			];

			$this->placeHolders = [
				'id'                => $this->entry->getId(),
				'downloads'         => (int) $this->entry->getDownloads(),
				'type'              => $this->entry->getType(),
				'title'             => $title,
				'romanji'           => $this->getRomanji(),
				'cover'             => $this->getCover(),
				'released'          => $this->entry->getReleased(),
				'developerType'     => $developerType,
				'website'           => $this->getWebsite(),
				'information'       => $this->getInformation(),
				'siteType'          => $this->getSiteType(),
				'size'              => $this->getSize(),
				'password'          => $this->getPassword(),
				'images'            => $this->getImages(),
				'characters'        => $this->getCharacters(),
				'relations'         => $relationsHtml,
				'links'             => $this->getLinks(),
			];

			$this->ifs['single'] = $this->single;

			$this->fillFors();
			$this->fillIfs();
			$this->fillPlaceHolders();
		}

		/**
		 * @return string
		 */
		private function getTitle(): string
		{
			$this->title = $this->entry->getTitle();

			if ($this->entry->getType() == 'game') {
				return $this->title;
			}

			$entryDeveloperRepository = app('em')->getRepository(EntryDeveloper::class);
			$developerEntities = $entryDeveloperRepository->findBy(['entry' => $this->entry]);


			$specialDevelopers = ['survive'];
			foreach ($developerEntities as $developerEntity) {
				if (in_array($developerEntity->getDeveloper()->getName(), $specialDevelopers)) {
					return $this->title . ' (モーションコミック版)';
				}
			}

			return strpos($this->title, 'The Motion Anime') === false ? $this->title :
				$this->title = str_replace(substr($this->title, -7), '', $this->title);
		}

		/**
		 * @return string|null
		 */
		private function getRomanji(): ?string
		{
			return $this->entry->getRomanji() !== $this->title ? $this->entry->getRomanji() : null;
		}

		/**
		 * @return string
		 */
		private function getCover(): string
		{
			return isset($_SESSION['_18']) && $_SESSION['_18'] == '-' ? 'images/No Imagem.jpg' :
				getImages($this->entry, 'cover', 'l');
		}

		private function getDevelopers(): array
		{
			$entryDeveloperRepository = app('em')->getRepository(EntryDeveloper::class);
			$entities = $entryDeveloperRepository->findBy(['entry' => $this->entry]);

			$developers = [];

			foreach ($entities as $entity) {
				$developer = $entity->getDeveloper();

				$url = '?v=2&did=' . $developer->getId();
				$developers[] = ['url' => $url, 'name' => $developer->getName()];
			}
			$tmp = [];
			return array_filter($developers, function ($developer) use (&$tmp) {
				if (! in_array($developer['name'], $tmp)) {
					$tmp[] = $developer['name'];
					return true;
				}
				return false;
			});
		}

		/**
		 * @return string|null
		 */
		private function getWebsite(): ?string
		{
			return $this->entry->getWebsite() ?: null;
		}

		/**
		 * @return string|null
		 */
		private function getInformation()
		{
			return $this->entry->getInformation() ?: null;
		}

		/**
		 * @return string
		 */
		private function getSiteType(): string
		{
			$site = $this->entry->getInformation();
			if (strpos($site, 'getchu') !== false) {
				return "Getchu:";
			} else if (strpos($site, 'dlsite') !== false) {
				return "DLsite:";
			} else {
				return "Information:";
			}
		}

		/**
		 * @return string|null
		 */
		private function getSize(): ?string
		{
			return $this->entry->getSize() ?: null;
		}

		/**
		 * @return string|bool
		 */
		private function getPassword(): ?string
		{
			return $this->entry->getPassword() ?: null;
		}

		/**
		 * @return string|bool
		 */
		private function getImages(): ?string
		{
			$images = new Images($this->entry);

			$images->createImages(true);

			return $images->createImages();
		}

		/**
		 * @return string|bool
		 */
		private function getCharacters(): ?string
		{
			$characters = new Characters($this->entry);

			return $characters->createCharacters();
		}

		/**
		 * @return string|bool
		 */
		private function getRelations(): ?string
		{
			$relations =  new Relations($this->entry);

			$relations->buildContent($this->entry);

//			return $relations->createRelations();
			return $relations->getContent();
		}

		/**
		 * @return string|bool
		 */
		private function getLinks(): ?string
		{
			if ($this->entry->getType() == 'app') {
				//return '';
			}
			$links = new Links2($this->entry);

			$linkBox = $links->createLinks();

			$this->single = $links->getSingle();


			return $linkBox;
		}

		private function getSharingUrls(): array
		{
			$threadRepository = app('em')->getRepository(Thread::class);
			$entities = $threadRepository->findBy(['entry' => $this->entry->getId()]);

			$threads = [];

			/** @var Thread $thread */
			foreach ($entities as $nr => $thread) {
				$url = $thread->getUrl();

				$key = 'sharing_url_' . $nr;
				$threads[] = [
					'id'        => $thread->getId(),
					'entry-id'  => $this->entry->getId(),
					'type'      => $this->entry->getType(),
					'nr'        => $nr,
					'author'    => $thread->getAuthor(),
					$key        => $url,
				];
			}

			if (! $threads) {
				$threads[] = [
					'id'        => 0,
					'entry-id'  => $this->entry->getId(),
					'type'      => $this->entry->getType(),
					'nr'        => 0,
					'author'    => 'yuuichi_sagara',
					'sharing_url_0' => '',
				];
			}

			return $threads;
		}
	}
?>
