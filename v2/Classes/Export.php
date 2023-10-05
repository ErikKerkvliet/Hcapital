<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 18-3-20
	 * Time: 22:49
	 */

	namespace v2\Classes;


	use v2\Database\Entity\Entity;
	use v2\Database\Entity\EntryCharacter;
	use v2\Database\Entity\Entry;
	use \v2\Database\Entity\Developer;
	use v2\Database\Entity\EntryDeveloper;
	use v2\Database\Entity\EntryRelation;
	use v2\Database\Entity\Link;
	use v2\Database\Repository\DeveloperRelationRepository;
	use v2\Database\Repository\EntryCharacterRepository;
	use v2\Database\Repository\EntryDeveloperRepository;
	use v2\Database\Repository\EntryRelationRepository;
	use v2\Database\Repository\EntryRepository;
	use v2\Database\Repository\LinkRepository;

	class Export extends TextHandler
	{
		/**
		 * @var null|Entry
		 */
		private $entry = null;

		private $multiple;

		private $type = '';

		public function __construct($entry, $type, $multiple = false)
		{
			$this->entry = $entry;
			$this->type = $type;
			$this->multiple = $multiple;

			$file = fopen(\v2\Manager::TEMPLATE_FOLDER . 'Export.html', 'r');
			$this->content = fread($file, 100000);

			$this->cssFiles = [];

			$this->jsFiles = [];
		}

		public function buildContent()
		{
			$this->placeHolders = [
				'export' => $this->getExportEntry(),
			];

			$this->fillPlaceHolders();

		}

		private function getExportEntry()
		{
			$entries = [];
			/** @var EntryRepository $entryRepository */
			$entryRepository = app('em')->getRepository(Entry::class);

			if ($this->multiple) {
				if ($this->type === 'entry') {
					$entries = $entryRepository->findExportEntries($this->entry);
					$entries = array_reverse($entries);
				}
				if ($this->type === 'link') {
					$entries = $entryRepository->findByLastEdited($this->entry->getLastEdit());
				}
			} else {
				$entries[] = $this->entry;
			}
			$variables = [];
			$entriesString = [];

			/** @var Entry $entry */
			foreach ($entries as $entry) {
				$this->entry = $entry;
				if ($this->type === 'link') {
					$entriesString[] = $this->getRapidgatorLinks() ?: '';
				} else {
					$variables[] = 'id|!|' . saveForSql($entry->getId());
					$variables[] = 'title|!|' . saveForSql($entry->getTitle());
					$variables[] = 'romanji|!|' . saveForSql($entry->getRomanji());
					$variables[] = 'released|!|' . saveForSql($entry->getReleased());
					$variables[] = 'size|!|' . saveForSql($entry->getSize());
					$variables[] = 'website|!|' . saveForSql($entry->getWebsite());
					$variables[] = 'information|!|' . saveForSql($entry->getInformation());
					$variables[] = 'password|!|' . saveForSql($entry->getPassword());
					$variables[] = 'type|!|' . saveForSql($entry->getType());
					$variables[] = 'timeType|!|' . saveForSql($entry->getTimeType());

					$entryString = implode('|?|', $variables) . '|-|';

					$entryString .= $this->getExportDevelopers() ?: '';
					$entryString .= $this->getExportCharacters() ?: '';
					$entryString .= $this->getExportLinks() ?: '';
					$entryString .= $this->getExportRelations() ?: '';

					$entriesString[] = $entryString;

					$variables = [];
				}
			}

			return implode('|^|', $entriesString);
		}

		private function getExportDevelopers()
		{
			/** @var EntryDeveloperRepository $entryDeveloperRepository */
			$entryDeveloperRepository = app('em')->getRepository(EntryDeveloper::class);
			$developers = $entryDeveloperRepository->findDevelopersByEntry($this->entry);

			$variables = [];
			$developersString = [];

			/** @var Developer $developer */
			foreach ($developers as $developer) {
				$variables[] = 'name|!|' . saveForSql($developer->getName());
				$variables[] = 'type|!|' . saveForSql($developer->getType());

				$developersString[] = implode('|?|', $variables);

				$variables = [];
			}
			return implode('|*|', $developersString) . '|-|';
		}

		private function getExportRelations()
		{
			/** @var EntryRelationRepository $entryRelationRepository */
			$entryRelationRepository = app('em')->getRepository(EntryRelation::class);

			$entryType = $this->entry->getType();
			$relationString = [];
			if ($entryType == 'ova' || $entryType == '3d') {
				$entryRelations = $entryRelationRepository->findByEntry($this->entry);
				if (count($entryRelations) < 1) {
					return '|-|';
				}
				$relationString[] = $this->getExportString($entryRelations[0]);
			} else {
				$entries = $entryRelationRepository->findBy(['entry' => $this->entry]);
				$relations = $entryRelationRepository->findBy(['relation' => $this->entry]);

				$relations = array_merge($relations, $entries);
				foreach ($relations as $relation) {
					$relationString[] = $this->getExportString($relation);
				}
			}
			return implode('|*|', $relationString) . '|-|';
		}

		private function getExportCharacters()
		{
			/** @var EntryCharacterRepository $entryCharacterRepository */
			$entryCharacterRepository = app('em')->getRepository(EntryCharacter::class);
			$entryCharacters = $entryCharacterRepository->findCharactersByEntry($this->entry);

			$charactersString = [];
			foreach ($entryCharacters as $entryCharacter) {
				$charactersString[] = $this->getExportString($entryCharacter);
			}
			return implode('|*|', $charactersString) . '|-|';
		}

		private function getExportLinks()
		{
			/** @var LinkRepository $linkRepository */
			$linkRepository = app('em')->getRepository(Link::class);
			$links = $linkRepository->findBy(['entry' => $this->entry]);

			$linksString = [];
			/** @var Link $link */
			foreach ($links as $link) {
				$linksString[] = $this->getExportString($link);
			}
			return implode('|*|', $linksString) . '|-|';
		}

		private function getExportString($entity)
		{
			if ($entity instanceof Link && substr($entity->getComment(), 0, 2) == '<<') {
				$entity->setComment('&lt;&lt;' . substr($entity->getComment(), 2));
			}
			$methods = array_filter(get_class_methods($entity), function ($method) use ($entity) {
				if (substr($method, 0, 3) != 'get') {
					return false;
				}
				if ($method == 'getId' || $method == 'getOriginalValues') {
					return false;
				}
				return true;
			});
			$variables = [];

			if (get_class($entity) == 'v2\Database\Entity\Character') {
				array_unshift($methods, 'getId');
			}

			if ($entity::TABLE == 'entry_relations') {
				$entity->setEntry($entity->getEntry()->getId());
				$entity->setRelation($entity->getRelation()->getId());
			}
			foreach ($methods as $method) {
				$key = lcfirst(substr($method, 3));
				$variables[] = $key . '|!|' . $entity->{$method}(true);
			}
			return implode('|?|', $variables);
		}

		private function getRapidgatorLinks()
		{
			$linkRepository = app('em')->getRepository(Link::class);

			$links = $linkRepository->findRapidgatorLinksByEntry($this->entry);

			$linkData = [];
			/** @var Link $link */
			foreach ($links as $link) {
				$data = [];
				$data[] = 'entry|!|' . $link->getEntry(true);
				$data[] = 'link|!|' . $link->getLink();
				$data[] = 'part|!|' . $link->getPart();
				$data[] = 'comment|!|' . $link->getComment();

				$linkData[] = implode('|?|', $data);
			}
			return implode('|?|', $linkData);
		}
	}