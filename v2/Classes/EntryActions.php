<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 25-2-20
	 * Time: 19:28
	 */

	namespace v2\Classes;

	use v2\Classes\DeleteHandler;
	use v2\Resolvers\HostResolver;
	use v2\Resolvers\LinkResolver;
	use v2\Database\Entity\Banned;
	use v2\Database\Entity\Broken;
	use v2\Database\Entity\Download;
	use v2\Database\Entity\EntryCharacter;
	use v2\Database\Entity\DeveloperRelation;
	use v2\Database\Entity\Entry;
	use v2\Database\Entity\EntryDeveloper;
	use v2\Database\Entity\Developer;
	use v2\Database\Entity\Character;
	use v2\Database\Entity\EntryRelation;
	use v2\Database\Entity\Host;
	use v2\Database\Entity\Link;
	use v2\Database\Entity\Thread;
	use v2\Database\Repository\DeveloperRelationRepository;
	use v2\Database\Repository\EntryDeveloperRepository;
	use v2\Database\Repository\EntryRelationRepository;
	use v2\Database\Repository\EntryRepository;
	use v2\Database\Repository\LinkRepository;
	use v2\Manager;

	class EntryActions
	{
		const RELATION_TYPE_SERIES = 'series';

		private $imageHandler = null;

		private $insert = false;
		private $id = 0;
		private $newId;
		private $title;
		private $romanji;
		private $type;
		private $timeType;
		private $released;
		private $website;
		private $information;
		private $vndb = 0;
		private $password;
		private $size;
		private $developers = [];
		private $rapidgatorLinks = [];
		private $mexashareLinks = [];
		private $katfileLinks = [];
		private $ddownloadLinks = [];
		private $fikperLinks = [];
		private $rosefileLinks = [];
		private $linkResolver = null;
		private $hostResolver;
		private $imagePath;
		private $outputDir;
		private $cover;
		private $coverHidden;
		private $images = [];
		private $entryId = 0;

		private $deleteHandler = null;
		private $appId = null;

		public function __construct($insert = false, $id = 0)
		{
			$this->imageHandler = new ImageHandler('entry');
			$this->hostResolver = new HostResolver();
			$this->linkResolver = new LinkResolver();
			$this->deleteHandler = new DeleteHandler();
			$this->insert = $insert;

			$this->id = $id ?: (requestForSql('entry-id') ?: requestForSql('id'));
			$this->newId = request('new-id', 0);
			$this->title = request('title');
			$this->romanji = request('romanji');
			$this->type = request('type');
			$this->timeType = request('time_type');
			$this->released = str_replace('-', '/', requestForSql('released'));
			$this->size = $this->getSize(requestForSql('size'));
			$this->website = request('website');
			$this->information = request('information');
			$this->vndb = $this->extractVndb(request('vndb'));
            $this->password = requestForSql('password');
            $this->appId = requestForSql('app-id');
			
			foreach (Host::HOSTS as $host) {
				$variable = $host . 'Links';
				$this->{$variable} = requestForSql($host . '-links');
			}

			$this->cover = request('cover');
			$this->coverHidden = request('cover_hidden');
			$this->images = request('images');
			$this->imagePath = '/home/erik/Desktop/img';
			$this->outputDir = getcwd() . '/entry_images/entries';
		}

		public function doAction($action) {
			$this->{$action}();
		}

		private function insert()
		{
			$this->insertEditEntry();

			if (! $this->id) {
				dd('Some query error.');
			}

			$this->insertDevelopers();

			$this->insertRelations();

			$this->insertEditLinks();

			if (AdminCheck::checkForLocal() && requestForSql('id') == $this->newId) {
				$this->setupFolders();

				$this->insertEditImages();
			}
			header('Location: /?v=2&id=' . $this->id);
		}

		private function update()
		{
			$this->insert();
		}

		public function import()
		{
			$importText = request('importText');

			if (substr($importText, 0, 4) === 'http') {
				$this->importPureLinks($importText);
				header('Location: /');
				die();
			}
			if (substr($importText, 0, 5) === 'entry') {
				$this->updateLinks($importText);
				header('Location: /');
				die();
			}
			$importEntries = explode('|^|', $importText);

			foreach ($importEntries as $importEntry) {
				$importParts = explode('|-|', $importEntry);

				$i = 0;
				foreach ($importParts as $key => $parts) {
					if (! $importParts[$i]) {
						continue;
					}
					$this->importEntries($importParts[$i]);
					$i++;

					$this->importDevelopers($importParts[$i]);
					$i++;

					$this->importCharacters($importParts[$i]);
					$i++;

					$this->importLinks($importParts[$i]);
					$i++;

					$this->importRelations($importParts[$i]);
					$i++;
				}
			}
			header('Location: /?v=2&id=' . $this->entryId);
		}

		private function insertEditEntry()
		{
			$date = date('Y-m-d H:i:s');
			if ($this->insert) {
				$entryRepository = app('em')->getRepository(Entry::class);
				$existingEntry = $entryRepository->findOneBy(['title' => $this->title]);

				if ($existingEntry) {
					dd('Duplicate entry: ' . $existingEntry->getId());
				}

				$entry = new Entry();
				$entry->setCreated($date);
				$entry->setLastEdit($date);
			} else {
				$entry = app('em')->find(Entry::class, $this->id);
				if ($this->appId == 'browser') {
					$entry->setLastEdit($date);
				}
				
				if ($this->id !== $this->newId) {
					// dd($this->id, $this->newId, $this->id == $this->newId);
					// $this->updateId($entry);
				}
			}

			$entry->setTitle($this->title);
			$entry->setRomanji($this->romanji);
			$entry->setType($this->type);
			$entry->setTimeType($this->timeType);
			$entry->setReleased($this->released);
			$entry->setSize($this->size);
			$entry->setWebsite($this->website);
			$entry->setInformation($this->information);
			$entry->setVndb($this->vndb);
			$entry->setPassword($this->password);

			if (! $this->insert) {
				$entry->setTimeType('old');
			}

			if ($this->insert) {
				$this->id = app('em')->flush($entry, true);
			} else {
				app('em')->update($entry);
				app('em')->flush();
			}
		}

		private function insertDevelopers()
		{
			/** @var EntryDeveloperRepository $entryDeveloperRepository */
			$entryDeveloperRepository = app('em')->getRepository(EntryDeveloper::class);
			$developerRepository = app('em')->getRepository(Developer::class);

			$developers = $this->id ? $entryDeveloperRepository->findDevelopersByEntry($this->id) : [];

			$i = 0;
			while (request('developer-' . $i) || request('developer-select-' . $i)) {
				$name = request('developer-' . $i) ?: request('developer-select-' . $i);
				if (is_numeric($name)) {
					$developer = $developerRepository->findOneBy(['id' => (int) $name, 'type' => $this->type]);
				} else {
					$developer = $developerRepository->findOneBy(['name' => $name, 'type' => $this->type]);
				}

				if ($developer) {
					if (in_array($developer, $developers)) {
						$i++;
						continue;
					} else {
						$this->insertEntryDeveloper($developer);
					}
				} else {
					$this->insertDeveloper($name);
				}
				$i++;
			}
		}

		private function insertDeveloper($name)
		{
			$developer = new Developer();
			$developer->setName($name);
			$developer->setType($this->type);

			app('em')->persist($developer);

			$id = app('em')->flush(null, true);

			$this->insertEntryDeveloper($id);

			return $id;
		}

		private function insertEntryDeveloper($developer)
		{
			$developer = is_numeric($developer) ? $developer : $developer->getId();

			$entryDeveloper = new EntryDeveloper();

			$entryDeveloper->setEntry($this->id);
			$entryDeveloper->setDeveloper($developer);

			app('em')->persist($entryDeveloper);

			app('em')->flush($entryDeveloper);
		}

		private function insertRelations()
		{
			$i = 0;
			while ($relation = request('relation-' . $i)) {
				$relation = app('em')->find(Entry::class, $relation);

				$this->insertRelation($relation, request('relation-type-' . $i));
				$i++;
			}
		}

		private function insertRelation(Entry $relation, $type)
		{
			$entryRelation = new EntryRelation();

			if ($type == 'series') {
				$entryRelation->setEntry($this->id);
				$entryRelation->setRelation($relation->getId());
			} else {
				$entryRelation->setEntry($relation->getId());
				$entryRelation->setRelation($this->id);
			}
			$entryRelation->setType($type);

			app('em')->persist($entryRelation);

			app('em')->flush();

			if ($type == 'series') {
				$relationRepository = app('em')->getRepository(EntryRelation::class);
				$exists = $relationRepository->findBy(['entry' => $relation->getId()]);

				if (! $exists) {
					$entryRelation = new EntryRelation();

					$entryRelation->setEntry($relation->getId());
					$entryRelation->setRelation($relation->getId());
					$entryRelation->setType($type);
				}
			} else {
				$entryRelation = new EntryRelation();

				$type = $this->switchRelationType($type);

				$entryRelation->setEntry($this->id);
				$entryRelation->setRelation($relation->getId());
				$entryRelation->setType($type);
			}

			app('em')->persist($entryRelation);

			app('em')->flush();

//			app('em')->dumpResults();
		}

		/**
		 * @param string $type
		 * @return string
		 */
		public function switchRelationType(string $type)
		{
			switch ($type) {
				case 'includes':
					return 'pack';
				case 'pack':
					return 'includes';
				case 'prequel':
					return 'sequel';
				case 'sequel':
					return 'prequel';
				case 'parent story':
					return 'side story';
				case 'side story':
					return 'parent story';
				default:
					return $type;
			}
		}

		public function insertEditLinks()
		{
			if (! $this->insert) {
				$this->deleteHandler->deleteLinks($this->id);
			}

			$i = 0;
			$j = 0;
			$l = [];
			while (isset($_POST['links-' . $i . '-links'])) {
				$linkString = trim(request('links-' . $i . '-links'), '\r\n');
				$comment = request('links-' . $j . '-comment') ?: '';
				echo $linkString . '<br>';
				$links = preg_split('/\r\n|[\r\n]/', $linkString);


				$links = array_filter($links);
				foreach ($links as $string) {
					if (! $string) {
						continue;
					}
					$link = new Link();

					$link->setEntry($this->id);
					if (substr($string, -3) == '.ra') {
						$string .= 'r';
					}
					$link->setLink($string);
					$link->setComment($comment);

					$part = $this->getPart($string);
					$link->setPart($part);

					app('em')->persist($link);
					$l[] = $link;
					app('em')->flush();
				}
				$i++;
				if ($i % count(Host::HOSTS) == 0) {
					$j++;
				}
			}
		}

		private function insertEditImages()
		{
//			if (! $this->insert) {
//				$imageDir = $this->outputDir . '/' . $this->id . '/cg/';
//				$images = scandir($imageDir);
//				unset($images[0]);
//				unset($images[1]);
//
//				$newCurrentImages = request('image') ?: [];
//				foreach ($images as $key => $image) {
//					if (! in_array($image, $newCurrentImages)) {
//						unlink($imageDir . $image);
//					}
//				}
//			}
			if (AdminCheck::checkForLocal()) {
				$images = $this->images;
				$this->images = [];
				$tmpImages = [];
				if ($images['name'][0]) {
					for ($i = 0; $i < count($images['name']); $i++) {
						$image = [
							'name' => $images['name'][$i],
							'tmp' => $images['tmp_name'][$i],
						];
						$tmpImages = array_merge($tmpImages, [$image]);
					}
					$this->images = array_merge($this->images, ['cg' => $tmpImages]);
				}

				if ($this->cover['name'] || $this->coverHidden) {
					if ($this->coverHidden) {
						$cover = [[
							'name' => '_cover_.jpg',
							'tmp' => '/home/erik/Desktop/img/_cover_.jpg',
						]];
					} else {
						$cover = [[
							'name' => $this->cover['name'],
							'tmp' => $this->cover['tmp_name'],
						]];
					}
					$this->images = array_merge(['cover' => $cover], $this->images);
				}
				$this->imageHandler->manipulate($this->id, $this->images, $this->type);
			}
		}

		private function getImageDimensions($image, $name)
		{
			$maxXY = strpos($name, $this->cover) === false ? 600 : ($name == ($this->cover . '_l') ? 700 : 320);

			$imageWidth = $image->getImageWidth();
			$imageHeight = $image->getImageHeight();

			$factorX = $maxXY / $imageWidth;
			$factorY = $maxXY / $imageHeight;
			$factor = 0;

			if ($factorX <= $factorY) {
				$factor = $factorX;
			} else if ($factorX > $factorY)	{
				$factor = $factorY;
			}
			if ($factorX > 1 && $factorY > 1) {
				$factor = 1;
			}

			return [
				'width' => floor($imageWidth * $factor),
				'height' => floor($imageHeight * $factor),
			];
		}

		private function setupFolders()
		{
			if (! file_exists($this->outputDir)) {
				dd('Incorrect output path.');
			}
			$dir = $this->outputDir . '/' . $this->id;
			if (! file_exists($dir)) {
				mkdir($dir);
				mkdir($dir . '/cover');
				mkdir($dir . '/cg');

				chmod($dir, 0777);
				chmod($dir . '/cover', 0777);
				chmod($dir . '/cg', 0777);
				return;
			}
			if (! file_exists($dir . '/cover')) {
				mkdir($dir . '/cover');
				chmod($dir . '/cover', 0777);

			}
			if (! file_exists($dir . '/cg')) {
				mkdir($dir . '/cg');
				chmod($dir . '/cg', 0777);
			}
		}

		private function getPart($link): int
		{
			$exploded = explode('/', $link);

			$fileName = end($exploded);

			if (strpos($fileName, '.part') !== false) {
				$fileNameParts = explode('.', $fileName);
				$part = str_replace('part', '', $fileNameParts[1]);
				return (int) $part;
			}
			return 0;
		}

        private function extractVndb($vndb): int
        {
            return (int) filter_var($vndb, FILTER_SANITIZE_NUMBER_INT);
        }

		private function getSize($requestSize)
		{
			$requestSize = str_replace(' ', '', $requestSize);
			$requestSize = str_replace('MB', '', $requestSize);
			$requestSize = str_replace('GB', '', $requestSize);

			if (! $requestSize) {
				return '';
			}
			if (strpos($requestSize, '.') !== false) {
				return $requestSize . ' GB';
			}
			return $requestSize . ' MB';
		}

		private function importEntries($entries)
		{
			$entryDataStrings = $this->splitByCharacters($entries, '|?|');

			$entryData = [];
			foreach ($entryDataStrings as $entryDataString) {
				$entryDataArray = $this->splitByCharacters($entryDataString, '|!|');

				$entryData = array_merge($entryData, [$entryDataArray[0] => $entryDataArray[1]]);
			}

			$exists = app('em')->find(Entry::class, $entryData['id']);

			if ($exists) {
				dd('Entry already exists.');
			}

			$entry = new Entry($entryData['id']);

			$this->entryId = $entryData['id'];

			$this->fillEntity($entryData, $entry);
		}

		private function importDevelopers($developers)
		{
			$developerDataStrings = $this->splitByCharacters($developers, '|*|');

			$developerRepository = app('em')->getRepository(Developer::class);
			foreach($developerDataStrings as $developerDataString) {
				$data = explode('|?|', $developerDataString);
				$developerData = [];
				$developer = null;
				foreach($data as $value) {
					$developerDataArray = $this->splitByCharacters($value, '|!|');
					$developerData = array_merge($developerData, [$developerDataArray[0] => $developerDataArray[1]]);

					$developer = $developerRepository->findOneBy(['name' => $developerData['name']]);
				}
				$developerId = 0;
				if (! $developer) {
					$developer = new Developer();

					$developerId = $this->fillEntity($developerData, $developer);
				}
				$developerId = $developerId ?: $developer->getId();

				if ($developerId) {
					$entryDeveloper = new EntryDeveloper();
					$entryDeveloper->setEntry($this->entryId);
					$entryDeveloper->setDeveloper($developerId);

					app('em')->persist($entryDeveloper);
					app('em')->flush();
				}
			}
		}

		private function importCharacters($characters)
		{
			if (! $characters) {
				return;
			}

			$characterDataStrings = $this->splitByCharacters($characters, '|*|');

			foreach($characterDataStrings as $characterDataString) {
				$data = explode('|?|', $characterDataString);
				$characterData = [];
				foreach($data as $value) {
					$characterDataArray = $this->splitByCharacters($value, '|!|');

					$characterData = array_merge($characterData, [$characterDataArray[0] => $characterDataArray[1]]);
				}
				$characterId = $characterData['id'];

				$character = new Character($characterId);
				$this->fillEntity($characterData, $character);

				/** @var EntryCharacter $entryCharacter */
				$entryCharacter = new EntryCharacter();
				$entryCharacter->setEntry($this->entryId);
				$entryCharacter->setCharacter($characterId);

				if ($entryCharacter) {
					app('em')->persist($entryCharacter);
					app('em')->flush();
				}
			}
		}

		private function importLinks($links)
		{
			if (! $links) {
				return;
			}
			$linkDataStrings = $this->splitByCharacters($links, '|*|');

			foreach($linkDataStrings as $linkDataString) {
				$data = explode('|?|', $linkDataString);
				$linkData = [];
				foreach($data as $value) {
					$linkDataArray = $this->splitByCharacters($value, '|!|');

					$linkData = array_merge($linkData, [$linkDataArray[0] => $linkDataArray[1]]);
				}
				$link = new Link();

				$this->fillEntity($linkData, $link);
			}
		}

		private function importRelations($relations)
		{
			if (! $relations) {
				return;
			}
			$relationDataStrings = $this->splitByCharacters($relations, '|*|');

			$entryRelationRepository = app('em')->getRepository(EntryRelation::class);
			foreach($relationDataStrings as $relationDataString) {
				$data = explode('|?|', $relationDataString);
				$relationData = [];
				foreach($data as $value) {
					$linkDataArray = $this->splitByCharacters($value, '|!|');

					$relationData = array_merge($relationData, [$linkDataArray[0] => $linkDataArray[1]]);
				}

				$entryRelation = new EntryRelation();
				$this->fillEntity($relationData, $entryRelation);

				$exists = $entryRelationRepository->findBy(['entry' => $relationData['relation']]);

				if (! $exists) {
					if ($relationData['relation']['type'] == self::RELATION_TYPE_SERIES) {
						$relationData['entry'] = $relationData['relation'];
					}
					$this->fillEntity($relationData, $entryRelation);
				}
			}
		}

		private function splitByCharacters($string, $splitCharacters)
		{
			$exploded = explode($splitCharacters, $string);

			return $exploded;
		}

		private function fillEntity($entityData, $entity)
		{
			unset($entityData['id']);
			foreach ($entityData as $key => $value) {
                $value = $value === 'app' ? 'game' : $value;
				$function = 'set' . ucfirst($key);
				$entity->{$function}($value);
			}
			return $this->insertEntity($entity);
		}

		private function insertEntity($entity)
		{
			app('em')->persist($entity);
			return app('em')->flush(null, true);
		}

		private function makeDirectories()
		{
			$directory = getcwd() . '/entry_images/entries/' . $this->id;

			if (! file_exists($directory)) {
				mkdir($directory);
				chmod($directory, 0777);

				mkdir($directory . '/cg');
				chmod($directory . '/cg', 0777);

				mkdir($directory . '/cover');
				chmod($directory . '/cover', 0777);

			}
		}

		public function updateLinks($data)
		{
			$linksData = explode('|^|', $data);

			/** @var LinkRepository $linkRepository */
			$linkRepository = app('em')->getRepository(Link::class);
			$entryIds = [];
			foreach($linksData as $data) {
				$propertiesData = explode('|?|', $data);
				$entryId = explode('|!|', $propertiesData[0])[1];

				$links = [];
				$linkData = [];
				foreach ($propertiesData as $propertyData) {
					$prop = explode('|!|', $propertyData);

					$linkData[$prop[0]] = $prop[1];

					if ($prop[0] === 'comment') {
						$links[] = $linkData;
					}
				}
				if (! in_array($entryId, $entryIds)) {
					$entryIds[] = $entryId;
					$host = $this->hostResolver->byUrl($linksData);

					if ($host == Host::HOST_MEXASHARE) {
						$this->deleteHandler->deleteByEntyAndHost((int) $entryId, '//mexa');
						$this->deleteHandler->deleteByEntyAndHost((int) $entryId, 'www.mexa');
						$this->deleteHandler->deleteByEntyAndHost((int) $entryId, 'mx-sh');
					} else {
						$this->deleteHandler->deleteByEntyAndHost((int) $entryId, $host);
					}
				}

				$entryId = 0;
				foreach ($links as $linkData) {
					$link = new Link();
					foreach ($linkData as $key => $value) {
						$setFunction = 'set' . ucfirst($key);
						$link->{$setFunction}($value);
						$entryId = $key == 'entry' ? $value : $entryId;
					}
					app('em')->persist($link);

					$entry = app('em')->find(Entry::class, $entryId);
					if ($entry) {
						$entry->setLastEdit(date('Y-m-d H:i:s'));
						app('em')->update($entry);
					}
				}
			}
			app('em')->flush();
		}

		private function updateId(Entry $entry) {
			$entryRepository = app('em')->getRepository(Entry::class);
			$entryRepository->updateId($this->id, $this->newId);

			$classes = [
				EntryCharacter::class,
				EntryDeveloper::class,
				EntryRelation::class,
				Link::class,
				Thread::class,
				Banned::class,
				Broken::class,
				Download::class,
			];
			
			foreach ($classes as $class) {
				$repository = app('em')->getRepository($class);

				$items = $repository->findBy(['entry' => $this->id]);
				
				foreach ($items as $item) {
					$item->setEntry($this->newId);
					app('em')->update($item);
				}

				if ($class == EntryRelation::class) {
					$items = $repository->findBy(['relation' => $this->id]);
					foreach ($items as $item) {
						$item->setRelation($this->newId);
						app('em')->update($item);
					}
				}
			}
			$this->id = $this->newId;

			app('em')->flush();		
		}

		private function importPureLinks($data)
		{
			$linksData = explode('http', $data);
			unset($linksData[0]);

			$entryLinks = [];
			foreach ($linksData as $link) {
				$filename = basename(parse_url($link, PHP_URL_PATH));
				if (preg_match('/^E(\d+)/', $filename, $matches)) {
					if (! isset($entryLinks[$matches[1]])) {
						$entryLinks[$matches[1]] = [];
					}
					$entryLinks[$matches[1]][] = 'http' . trim($link);
				}
			}

			/** @var LinkRepository $linkRepository */
			$linkRepository = app('em')->getRepository(Link::class);
			$newLinks = [];
			foreach ($entryLinks as $key => $entryLink) {
				$entryId = (int) $key;

				$links = $linkRepository->findBy(['entry' => $entryId]);
				// foreach($links as $link) {
				// 	app('em')->delete($link);
				// }
		
				$deletedHosts = [];
				foreach ($entryLink as $url) {
					$link = $this->linkResolver->byLinksAndUrl($links, $url);
					if ($link) {
						$ids[] = $link->getId();
						$link->setLink($url);
						
						$date = date('Y-m-d H:i:s');
						$link->setCreated($date);

						if (($comment = $link->getComment()) != '') {
							if (strpos($comment, ':') === false) {
								$comment .= ':';
								$link->setComment($comment);
							}
						}
						
						app('em')->update($link);
						app('em')->flush();
						continue;
					} else {
						$host = $this->hostResolver->byUrl($url);
						if (! isset($deletedHosts[$entryId])) {
							$deletedHosts[$entryId] = [];
						}
						// if (! in_array($host, $deletedHosts[$entryId])) {
						// 	$deletedHosts[$entryId][] = $host;

						// 	if ($host == Host::HOST_MEXASHARE) {
						// 		$this->deleteHandler->deleteByEntyAndHost((int) $entryId, '//mexa');
						// 		$this->deleteHandler->deleteByEntyAndHost((int) $entryId, 'www.mexa');
						// 		$this->deleteHandler->deleteByEntyAndHost((int) $entryId, 'mx-sh');
						// 	} else {
						// 		$this->deleteHandler->deleteByEntyAndHost((int) $entryId, $host);
						// 	}
						// }
						
						$newLink = new Link();
						$newLink->setLink($url);

						$parts = explode('.', $url);
						$newLink->setEntry($entryId);

						if (strpos($parts[1], '-') !== false) {
							$nameParts = explode('-', $parts[1]);
							$nameParts = str_replace('_', '', $nameParts);
						} else {
							$nameParts = explode('_', $parts[1]);
							$nameParts = str_replace('-', '', $nameParts);
						}
						if (count(($nameParts)) > 1) {
							unset($nameParts[0]);
							$comment = trim(implode(' ', $nameParts)) . ':';

							$first = substr($comment, 0, 1);
							$rest = substr($comment, 1);
							$comment = strtoupper($first) . $rest;
							
							$newLink->setComment($comment);
						}
						
						$part = 0;
						if ($parts[2] !== 'rar') {
							$part = substr($parts[2], 4);
						}
						$newLink->setPart((int) $part);
						
						$date = date('Y-m-d H:i:s');
						$newLink->setCreated($date);

						$newLinks[] = $newLink;
					}					
				}
				uasort($newLinks, function($a, $b) {
					return $a->getPart() <=> $b->getPart();
				});
				foreach($newLinks as $link) {
					app('em')->persist($link);
					app('em')->flush();
				}

				$entry = app('em')->find(Entry::class, $entryId);

				$date = date('Y-m-d H:i:s');
				$entry->setLastEdit($date);
				$entry->setTimeType('old');

				app('em')->update($entry);
				app('em')->flush();
			}
		}
	}