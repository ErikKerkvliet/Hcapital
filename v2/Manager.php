<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 10-12-19
	 * Time: 0:17
	 */

	namespace v2;

	use DeleteHandler;
	use v2\Classes\AdminCheck;
	use v2\Classes\CharacterActions;
	use v2\Classes\DeveloperActions;
	use v2\Classes\Downloads;
	use v2\Classes\EntryActions;
	use v2\Classes\ExportAdvanced;
	use v2\Classes\LastAdded;
	use v2\Classes\Latest;
	use v2\Classes\ListMain;
	use v2\Classes\Main;
	use v2\Classes\RandomEntries;
	use v2\Database\Entity\Banned;
	use v2\Database\Entity\Character;
	use v2\Database\Entity\Developer;
	use v2\Database\Entity\Download;
	use v2\Database\Entity\Entry;
	use v2\Database\Entity\EntryCharacter;
	use v2\Database\Entity\EntryDeveloper;
	use v2\Database\Entity\EntryRelation;
	use v2\Database\Entity\Link;
	use v2\Database\Entity\Thread;
    use v2\Database\Repository\CharacterRepository;
    use v2\Database\Repository\DownloadRepository;
    use v2\Database\Repository\EntryCharacterRepository;
	use v2\Database\Repository\EntryDeveloperRepository;
	use v2\Database\Repository\EntryRelationRepository;
	use v2\Database\Repository\EntryRepository;
	use v2\Database\Repository\LinkRepository;
    use v2\Transformers\CharacterTransformer;

	require_once("RapidgatorClient.php");

	require_once('Database/QueryHandler.php');
	require_once('Database/QueryBuilder.php');
	require_once('Database/Connection.php');

	require_once('Database/Entity/Entity.php');
	require_once('Database/Entity/Entry.php');
	require_once('Database/Entity/Log.php');
	require_once('Database/Entity/Banned.php');
	require_once('Database/Entity/Broken.php');
	require_once('Database/Entity/Character.php');
	require_once('Database/Entity/EntryCharacter.php');
	require_once('Database/Entity/Developer.php');
	require_once('Database/Entity/DeveloperRelation.php');
	require_once('Database/Entity/Download.php');
	require_once('Database/Entity/EntryRelation.php');
	require_once('Database/Entity/Link.php');
	require_once('Database/Entity/SeriesRelation.php');
	require_once('Database/Entity/EntryDeveloper.php');
	require_once('Database/Entity/Thread.php');
	require_once('Database/Entity/Host.php');

	require_once('Database/Repository/Repository.php');
	require_once('Database/Repository/BannedRepository.php');
	require_once('Database/Repository/BrokenRepository.php');
	require_once('Database/Repository/CharacterRepository.php');
	require_once('Database/Repository/EntryCharacterRepository.php');
	require_once('Database/Repository/DeveloperRepository.php');
	require_once('Database/Repository/DeveloperRelationRepository.php');
	require_once('Database/Repository/DownloadRepository.php');
	require_once('Database/Repository/EntryRepository.php');
	require_once('Database/Repository/EntryRelationRepository.php');
	require_once('Database/Repository/LinkRepository.php');
	require_once('Database/Repository/SeriesRelationRepository.php');
	require_once('Database/Repository/EntryDeveloperRepository.php');
	require_once('Database/Repository/ThreadRepository.php');
	require_once('Database/Repository/HostRepository.php');

	require_once('Traits/Builder.php');
	require_once('Builders/Images.php');
	require_once('Builders/Characters.php');
	require_once('Builders/Links.php');
	require_once('Database/EntityManager.php');

	require_once('Classes/AdminCheck.php');

	require_once('Traits/TextHandler.php');
	require_once('Classes/Borders.php');

	require_once('Classes/Relations.php');

	require_once('Classes/EntryInfo.php');
	require_once('Classes/GameList.php');
	require_once('Classes/OvaList.php');
	require_once('Classes/DeveloperList.php');
	require_once('Classes/CharacterList.php');
	require_once('Classes/ListMain.php');
	require_once('Classes/Searcher.php');
	require_once('Classes/OrderBar.php');
	require_once('Classes/Character.php');
	require_once('Classes/Developer.php');
	require_once('Classes/ListSelect.php');
	require_once('Classes/Developers.php');
	require_once('Classes/RandomEntries.php');

	require_once('Classes/EntryImages.php');

	require_once('Classes/EntryLinks.php');
	require_once('Classes/EntryRelations.php');
	require_once('Classes/FileHandler.php');

	require_once('Classes/Footer.php');
	require_once('Classes/Head.php');
	require_once('Classes/Header.php');
	require_once('Classes/EntryInfo.php');
	require_once('Classes/Home.php');
	require_once('Classes/Upcoming.php');
	require_once('Classes/LastAdded.php');
	require_once('Classes/Latest.php');
	require_once('Classes/Navigator.php');
	require_once('Classes/Components/AddDeveloper.php');
	require_once('Classes/Components/AddRelation.php');
	require_once('Classes/Components/AddLink.php');
	require_once('Classes/Components/AddSharingUrl.php');
	require_once('Classes/Components/IpData.php');
	require_once('Classes/InsertEdit.php');
	require_once('Classes/InsertEditCharacter.php');
	require_once('Classes/InsertEditDeveloper.php');
	require_once('Classes/Export.php');
	require_once('Classes/ExportAdvanced.php');
	require_once('Classes/Import.php');
	require_once('Classes/Downloads.php');
	require_once('Classes/LinkState.php');
	require_once('Classes/ImageHandler.php');
	require_once('Classes/DeleteHandler.php');
	require_once('Classes/EntryActions.php');
	require_once('Classes/CharacterActions.php');
	require_once('Classes/DeveloperActions.php');
	require_once('Classes/CreatePostData.php');
	require_once('Resolvers/LinkResolver.php');
	require_once('Resolvers/HostResolver.php');
	require_once('Resolvers/EntryNameResolver.php');
	require_once('Factories/ThreadFactory.php');
	require_once('Factories/LinkFactory.php');
    require_once('Transformers/CharacterTransformer.php');
    require_once('Transformers/EntryTransformer.php');

	require_once('Classes/Main.php');

	class Manager
	{
		CONST TEST = false;

		CONST CSS_JS_VERSION = 2.39;

		CONST TEMPLATE_FOLDER = 'v2/Templates/';
		CONST COMPONENT_FOLDER = 'v2/Templates/Components/';

		public function __construct()
		{
			setupGlobals();
//			/** @var EntryRepository $entryRepository */
//			$entryRepository = app('em')->getRepository(Entry::class);
//			$entries = $entryRepository->findRandomEntries(5);
//			dd($entries);
//			if (AdminCheck::checkForAdmin()) {
//				$repoLink = app('em')->getRepository(Link::class);
//				for($i = 1; $i < 8000; $i++) {
//					$count = 0;
//					$links = $repoLink->findBy(['entry' => $i]);
//					foreach ($links as $link) {
//						if (strpos($link->getLink(), 'E0') !== false) {
//							$count++;
//						}
//					}
//					if ($count > 1) {
//						dc($i);
//					}
//				}
//				dd();
//			}
			require_once('Traits/TextHandler.php');

			require_once('Classes/Main.php');
			$admin = AdminCheck::checkForAdmin();

			if (request('action') == 'getenv' && $admin) {
				$keys = request('keys');
				$envs = [];
				foreach ($keys as $key) {
					$envs[$key] = getenv($key);
				}
				if (count($envs) > 0) {
					echo json_encode([
						'success' => true,
						'envs' => $envs,
					]);
				} else {
					echo json_encode([
						'success' => false,
					]);
				}
				die();
			}

			if (request('action') == 'add' && $admin) {
				$nr = request('length');
				if (request('type') == 'developer') {
					$developerType = request('developerType');
					$developer = new \AddDeveloper($nr, $developerType);
					$developer->buildContent();
					$content = $developer->getContent();
				} else if (request('type') == 'relation') {
					$relation = new \AddRelation($nr, request('entryType'));
					$relation->buildContent();
					$content = $relation->getContent();
				} else if (request('type') == 'links') {
					$links = new \AddLink($nr);
					$links->buildContent();
					$content = $links->getContent();
				} else if (request('type') == 'sharingUrl') {
					$entryId = request('entryId');
					$threadId = request('threadId');
					$author = request('author');
					$links = new \AddSharingUrl($nr, $entryId, $threadId, $author);
					$links->buildContent();
					$content = $links->getContent();
				} else if (request('type') == 'ipData' && ($ipData = request('ipData'))) {
					$ipData = new \IpData($ipData);
					$ipData->buildContent();
					$content = $ipData->getContent();

				} else {
					echo json_encode([
						'success' => false,
					]);
					die;
				}
				echo json_encode([
					'content' => $content,
					'nr'      => $nr,
					'success' => true,
				]);
				die();
			}
			if (request('action') == 'insert' && $admin) {
				$entryAction = new EntryActions(true);
				$entryAction->doAction('insert');
			}
			if (request('action') == 'edit' && $admin) {
				$entryAction = new EntryActions(false, request('id'));
				$entryAction->doAction('update');
			}
			if (request('action') == 'insertDeveloper' && $admin) {
				new DeveloperActions(true);
			}
			if (request('action') == 'editDeveloper' && $admin) {
				new DeveloperActions(false, request('developer-id'));

				header('Location: ' . $_SESSION['referrer'] . '?v=2&did=' . request('did'));
			}
			if (request('action') == 'insertCharacter' && $admin) {
				new CharacterActions(true, request('entry-id'));
			}
			if (request('action') == 'editCharacter' && $admin) {
				new CharacterActions(false, request('cid'));

				header('Location: ' . $_SESSION['referrer'] . '?v=2&id=' . request('id'));
			}
			
			if (request('action') == 'confirm' && $admin && request('threadId') && request('state')) {
				$id = request('threadId');

				$thread = app('em')->find(Thread::class, $id);

				app('em')->update($thread);
				app('em')->flush();
				die();
			}
			if (request('action') == 'threadEdit' && $admin && request('threadId') && request('url')) {
				$threadId = request('threadId');
				$entryId = request('entryId') ?: null;

				/** @var Thread $thread */
				$thread = app('em')->find(Thread::class, $threadId);

				$thread->setUrl(request('url'));
				$thread->setAuthor(request('author'));

				if ($entryId) {
					$thread->setEntry($entryId);
				}
				app('em')->update($thread);
				app('em')->flush();
				die();
			}

			if (request('action') == 'delete' && ($entity = request('entity')) && $admin) {
				$deleteHandler = new DeleteHandler();
			
				switch ($entity) {
					case 'entry':
						if (request('entry')) {
							$entry = app('em')->find(Entry::class, request('entry'));
							$deleteHandler->deleteEntry($entry);
						}
					case 'entryCharacter':
						if (($entryId = request('entry')) && ($character = request('character'))) {
							$entry = app('em')->find(Entry::class, $entryId);
							$deleteHandler->deleteByEntryAndCharacter($entry, $character);
						}
						break;
					case 'entryRelation':
						if (($entry = request('entry')) && ($relation = request('relation'))) {
							$deleteHandler->deleteByEntryAndRelation($entry, $relation);
						}
						break;
					case 'entryDeveloper':
						if (($entry = request('entry')) && ($developer = request('developer'))) {
							$deleteHandler->deleteByEntryAndDeveloper($entry, $developer);
						}
						break;
					case 'developer':
						if (($developerId = request('developer'))) {
							$developer = app('em')->find(Developer::class, $developerId);
							$deleteHandler->deleteDeveloper($developer);
						}
						break;
					case 'character':
						if (($characterId = request('character'))) {
							$character = app('em')->find(Character::class,$characterId);
							$deleteHandler->deleteCharacter($character);
						}
						break;
					case 'link':
						if (($entry = request('entry')) && ($comment = request('comment'))) {
							$deleteHandler->deleteLinksByEntryAndComment($entry, $comment);
						}
						break;
					case 'thread':
						if (($entryId = request('entry')) && ($number = request('number'))) {
							$deleteHandler->deleteThreads($entry, $number);
						}
						break;
					case 'banned':
						if (($ip = request('ip'))) {
							$deleteHandler->deleteBanned($ip);
						}
						break;
					case 'download':
						$entry = request('entry');
						$date = request('date');

						$deleteHandler->deleteDownloads($entry, $date);
				}
				echo json_encode(['success' => true]);
				die();
			}
			if (request('action') === 'ban' && ($ip = request('ip'))) {
				$ban = new Banned();
				$ban->setIp($ip);

				app('em')->flush($ban);
				echo json_encode([
					'success' => true,
				]);
				die();
			}
			if (request('action') == 'random') {
				$random = new RandomEntries();
				if (request('refresh') === 'true') {
					$types = request('types') ?: ['ova', 'game'];
					$entries = $random->getRandomEntries($types);
				} else {
					$ids = explode('', request('ids'));
//					$entries = $random->loadEntries($ids);
				}

				echo json_encode([
					'entries' => $entries,
					'success' => true,
				]);
			}
			if (request('action') == 'characterFolder' && ($characterId = request('characterId')) && $admin) {
				$password = getenv('LOCAL_PC_PASSWORD');

				$descriptorspec = array(
					0 => array("pipe", "r"),  // stdin
					1 => array("pipe", "w"),  // stdout
					2 => array("pipe", "w")   // stderr
				);
				
				$process = proc_open("sudo -S thunar /home", $descriptorspec, $pipes);
				
				if (is_resource($process)) {
					fwrite($pipes[0], $password . "\n");
					$output = stream_get_contents($pipes[1]);
					$error = stream_get_contents($pipes[2]);
					$return_value = proc_close($process);
				
					if ($return_value == 0) {
						echo "Thunar is geopend in de /home directory.";
					} else {
						echo "Er is een fout opgetreden bij het openen van Thunar: " . $error;
					}
				} else {
					echo "Kan Thunar niet openen.";
				}
				die();
			}
            if (request('action') == 'searchCharacters' && ($search = request('search')) && $admin) {
                /** @var CharacterRepository $characterRepository */
                $characterRepository = app('em')->getRepository(Character::class);
                $characters = $characterRepository->findBySearch($search, ['name', 'desc'], []);

                $characterTransformer = new CharacterTransformer();
                $transformed = $characterTransformer->transform($characters);

                echo json_encode($transformed);
                exit();
            }
			
            $linkIds = [];
			if (request('action') === 'fileInfo'
				&& $admin
				&& ($fileIds = request('fileIds'))
					|| ($linkIds = request('linkIds'))
			) {
				if ($linkIds) {
					$fileIds = [];
					$linkRepository = app('em')->getRepository(Link::class);
					$links = $linkRepository->findById(explode(',', $linkIds));
					foreach($links as $link) {
						$url = $link->getLink();
						if (strpos($url, 'rapidgator') !== false
							|| strpos($url, 'rg.to') !== false )
						{
							$fileIds[$link->getId()] = explode('/', explode('file', $url)[1])[1];
						} else {
							$fileIds[$link->getId()] = null;
						}
					}
				} else {
					$fileIds = explode(',', $fileIds);
				}

				$user = getenv('RAPIDGATOR_USERNAME');
				$password = getenv('RAPIDGATOR_PASSWORD');
				$client = new RapidgatorClient($user, $password, null);

				$states = [];
				foreach ($fileIds as $key => $id) {
					if (! $id) {
						$status = 'null';
					} else {
						try {
							$status = $client->getFileDetails($id)->status === 200 ? 'success' : 'fail';
						} catch (ClientException $e) {
							$status = 'null';
						}
					}
					$states[] = $key . '-' . $status;
				}

				echo json_encode($states);
				die();
			}
			if (request('action') == 'getExportCode') {
				$entryIds = explode(',', request('entryIds'));
				$type = request('type');
				$multiple = request('multiple') === 'true';
				$from = (int) request('from');
				$to = (int) request('to');

				$advancedExport = new ExportAdvanced($entryIds, $type, $multiple, $from, $to);
				$exportData = $advancedExport->getExportData();
				echo json_encode([
					'exportCode' => $exportData['message'],
					'errors' => $exportData['errors'],
					'state' => ! $exportData['errors'],
				]);
				die();
			}

			if (request('a') == 'link') {
				$link = app('em')->find(Link::class, request('lid'));

				/** @var Entry $entry */
				$entry = $link->getEntry();

				$isAddDownload = ! AdminCheck::checkForAdmin() || AdminCheck::checkForLocal();
				if ($isAddDownload) {
					$entry->setDownloads($entry->getDownloads() + 1);

					app('em')->update($entry);
					app('em')->flush();
				}

                $url = '';
                $comment = '';
                $success = false;

                $download = new Download();
                if (AdminCheck::checkForAdmin()) {
                    $url = $link->getLink();
                    $success = true;
                } else {
                    $downloadRepository = app('em')->getRepository(Download::class);

                    $ipAddress = AdminCheck::get_ip_address();
                    $downloads = $downloadRepository->getDownloadsByIp($ipAddress, 1);
                    if (AdminCheck::isBanned($entry)) {
                        $comment = Banned::BANNED;
                        $url = Link::BANNED_URL;
                        $success = true;
                    } elseif (count($downloads) > 15) {
                        $comment = Download::TO_MANY_DOWNLOADS_LINK;
                    } elseif (count(array_unique(array_map(function ($download) {
                            return $download->getEntry(true);
                        }, $downloads))) > 5
                    ) {
                        $comment = Download::TO_MANY_DOWNLOADS_ENTRY;
                    } else {
                        $url = $link->getLink();
                        $success = true;
                    }
                    $download->setComment($comment);
                }

                $download->setEntry($entry->getId());
                $download->setLink($link->getId());
                $download->setIp(AdminCheck::get_ip_address());

				if ($isAddDownload) {
					app('em')->flush($download);
				}

				echo json_encode([
					'link' => $url,
                    'comment' => $comment,
					'success' => $success,
				]);
				die();
			} else if (request('a') == 'listItems') {
				$listItems = new ListMain();

				$items = $listItems->getList();

				$json = json_encode([
					'items'     => $items,
					'success'   => true,
				]);
				if ($json) {
					echo $json;
				} else {
					$items = iconv("UTF-8","UTF-8//IGNORE",$items);

					echo json_encode([
						'items'     => $items,
						'success'   => true,
					]);
				}

				die();
			} else if (request('timeType') && request('type')) {
				$lastGames = null;
				$lastOvas = null;
				$latestGames = null;
				$latestOvas = null;

				if (request('timeType') == 'first') {
					if (request('u')) {
						$latestGames = new Latest(request('ug'), 'game');
						$latestGames->buildContent();

						$latestOvas = new Latest(request('uo'), 'ova');
						$latestOvas->buildContent();
					} else {
						$lastGames = new LastAdded(request('og'), 'game');
						$lastGames->buildContent();

						$lastOvas = new LastAdded(request('oo'), 'ova');
						$lastOvas->buildContent();

						$latestGames = new Latest(request('ng'), 'game');
						$latestGames->buildContent();

						$latestOvas = new Latest(request('no'), 'ova');
						$latestOvas->buildContent();
					}
				} else if (request('timeType') == 'last') {
					if (request('type') == 'game') {
						$lastGames = new LastAdded(request('page'), 'game');
						$lastGames->buildContent();
					} else {
						$lastOvas = new LastAdded(request('page'), 'ova');
						$lastOvas->buildContent();
					}
				} else {
					if (request('type') == 'game') {
						$latestGames = new Latest(request('page'), 'game');
						$latestGames->buildContent();
					} else {
						$latestOvas = new Latest(request('page'), 'ova');
						$latestOvas->buildContent();
					}
					if (request('type') == 'game') {
						$latestGames = new Latest(request('page'), 'game');
						$latestGames->buildContent();
					} else {
						$latestOvas = new Latest(request('page'), 'ova');
						$latestOvas->buildContent();
					}
				}
				echo json_encode([
					'lastGames' => $lastGames ? $lastGames->getContent() : null,
					'lastOvas' => $lastOvas ? $lastOvas->getContent() : null,
					'latestGames' => $latestGames ? $latestGames->getContent() : null,
					'latestOvas' => $latestOvas ? $latestOvas->getContent() : null,
					'success' => true,
				]);
				die();
			}

			$main = new Main();

			$main->buildContent();
		}

		public function save($string) {
			$string = str_replace('\\', '', $string);

			$string = str_replace("'", "\'", $string);
			$string = str_replace('"', '\"', $string);

			$pattern = '/[^A-Za-z0-9\+\'\#\"\-\\\w\/:.\.ぁ-ゔァ-ヺー\x{4E00}-\x{9FAF}_\- ]+/u';
			$string = preg_replace($pattern, ' ', $string);

			$string = mysqli_real_escape_string(app('connection'), $string);

			$string = str_replace('#', '\#', $string);


			return $string;
		}
	}
