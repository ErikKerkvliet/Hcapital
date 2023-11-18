<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 10-12-19
	 * Time: 0:17
	 */

	namespace v2;

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
	use v2\Database\Repository\EntryCharacterRepository;
	use v2\Database\Repository\EntryDeveloperRepository;
	use v2\Database\Repository\EntryRelationRepository;
	use v2\Database\Repository\EntryRepository;
	use v2\Database\Repository\LinkRepository;

	require_once("Includes.php");
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
	require_once('Database/Entity/ToDo.php');
	require_once('Database/Entity/SeriesRelation.php');
	require_once('Database/Entity/EntryDeveloper.php');
	require_once('Database/Entity/Thread.php');
	require_once('Database/Entity/SharingThread.php');
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
	require_once('Database/Repository/ToDoRepository.php');
	require_once('Database/Repository/SeriesRelationRepository.php');
	require_once('Database/Repository/EntryDeveloperRepository.php');
	require_once('Database/Repository/ThreadRepository.php');
	require_once('Database/Repository/SharingThreadRepository.php');
	require_once('Database/Repository/HostRepository.php');

	require_once('Builders/Images.php');
	require_once('Builders/Builder.php');
	require_once('Builders/Characters.php');
	require_once('Builders/Links.php');
	require_once('Builders/Links2.php');
	require_once('Database/EntityManager.php');

	require_once('Classes/AdminCheck.php');

	require_once('Classes/TextHandler.php');
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
	require_once('Classes/EntryActions.php');
	require_once('Classes/CharacterActions.php');
	require_once('Classes/DeveloperActions.php');
	require_once('Classes/CreatePostData.php');
	require_once('Classes/Threads.php');
	require_once('Resolvers/LinkResolver.php');
	require_once('Resolvers/HostResolver.php');
	require_once('Resolvers/EntryNameResolver.php');
	require_once('Factories/ThreadFactory.php');
	require_once('Factories/LinkFactory.php');

	require_once('Classes/Main.php');

	class Manager
	{
		CONST TEST = false;

		CONST CSS_JS_VERSION = 2.35;

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
			require_once('Classes/TextHandler.php');

			require_once('Classes/Main.php');

			$admin = AdminCheck::checkForAdmin();
			if ($admin && request('id') && request('getLinksFromPython') && request('key')) {
				$key = request('key') === 'Nitc0sGyCZ^Jmvi2CB';
				$author = request('author');
				/** @var LinkRepository $linkRepository */
				$linkRepository = app('em')->getRepository(Link::class);
				$data = $linkRepository->getEntryLinks(request('id'), $author);
dd($data);
				if ($key) {
					echo $data;
				}

				die;
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
					$entryType = request('entryType');
					$author = request('author');
					$links = new \AddSharingUrl($nr, $entryId, $threadId, $entryType, $author);
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
				$characterAction = new CharacterActions(true, request('entry-id'));
			}
			if (request('action') == 'editCharacter' && $admin) {
				$characterAction = new CharacterActions(false, request('cid'));

				header('Location: ' . $_SESSION['referrer'] . '?v=2&id=' . request('id'));
			}
			if (request('action') == 'deleteCharacter' && $admin) {
				$entryId = request('entry');
				$characterId = request('character');

				$character = app('em')->find(Character::class, $characterId);

				/** @var EntryCharacterRepository $entryCharacterRepository */
				$entryCharacterRepository = app('em')->getRepository(EntryCharacter::class);

				$findBy = [
					'character_id' => $characterId,
				];

				if (($entry = request('entry'))) {
					$findBy['entry_id'] = $entry;
				}

				$entryCharacters = $entryCharacterRepository
					->findBy(['entry_id' => $entryId, 'character_id' => $characterId]);

				app('em')->delete($character);
				foreach ($entryCharacters as $entryCharacter) {
					app('em')->delete($entryCharacter);
				}

				echo json_encode([
					'success' => true,
				]);
				die();
			}
			if (request('action') == 'removeDeveloper') {
				$developer = app('em')->find(Developer::class, request('developerId'));

				app('em')->delete($developer);
			}
			if (request('action') == 'confirm' && $admin && request('threadId') && request('state')) {
				$id = request('threadId');
				$state = request('state') == 'on' ? false : true;

				$thread = app('em')->find(Thread::class, $id);

				$thread->setConfirmed($state);

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
			if (request('action') == 'removeRelation' && request('relationId') && $admin) {
				$entryId = request('entryId');
				$relationId = request('relationId');

				/** @var EntryRelationRepository $entryRelationRepository */
				$entryRelationRepository = app('em')->getRepository(EntryRelation::class);
				$entryRelations = $entryRelationRepository->findRelationsByEntryAndRelation($entryId, $relationId);

				foreach ($entryRelations as $entryRelation) {
					app('em')->delete($entryRelation);
				}
			}
			if (request('action') == 'removeDeveloper' && request('developerId') && $admin) {
				$entryId = request('entryId');
				$developerId = request('developerId');

				/** @var EntryDeveloperRepository $entryDeveloperRepository */
				$entryDeveloperRepository = app('em')->getRepository(EntryDeveloper::class);
				$entryDeveloper = $entryDeveloperRepository->findOneBy(['entry' => $entryId, 'developer' => $developerId]);

				app('em')->delete($entryDeveloper);
			}
			if (request('action') == 'removeEntryDeveloper' && $admin
				&& ($developerId = request('developerId')) && ($entryId = request('entryId'))
			) {
				$entryDeveloperRepository = app('em')->getRepository(EntryDeveloper::class);
				$entryDeveloper = $entryDeveloperRepository->findOneBy(['entry' => $entryId, 'developer' => $developerId]);

				app('em')->delete($entryDeveloper);
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
			if (request('action') === 'unban' && ($ip = request('ip'))) {
				$bannedRepository = app('em')->getRepository(Banned::class);
				$banned = $bannedRepository->findBy(['ip' => $ip]);

				foreach($banned as $ban) {
					app('em')->delete($ban);
				}
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
			if (request('action') == 'removeLinks' && request('comment') && $admin) {
				$entryId = request('entryId');
				$comment = request('comment');

				/** @var LinkRepository $linkRepository */
				$linkRepository = app('em')->getRepository(Link::class);
				$links = $linkRepository->findBy(['entry' => $entryId, 'comment' => $comment]);

				foreach ($links as $link) {
					app('em')->delete($link);
				}
			}
			if (request('action') === 'clearDownloads') {
				$findBy = [];
				$url = '/?v=2&action=di';
				if (($date = request('date'))) {
					$findBy['DATE_FORMAT(time, "%Y-%m-%d")'] = $date;
				}

				if (($entry = request('entry'))) {
					$findBy['entry'] = (int) $entry;
					$url .= '&entry=' . $entry;
				}

				if (! $findBy && request('all') !== 'true') {
					header('LOCATION: ' . $url);
					die();
				}

				$downloadRepository = app('em')->getRepository(Download::class);
				$downloads = $downloadRepository->findBy($findBy);

				app('em')->delete($downloads);

				header('LOCATION: ' . $url);
				die();
			}
			if (request('action') === 'fileInfo'
				&& ($user = request('user'))
				&& ($password = request('password'))
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
				$client = new RapidgatorClient($user, $password);

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
			if (request('action') === 'getExportCode') {
				$entryIds = explode(',', request('entryIds'));
				$type = request('type');
				$all = request('all') === 'true';
				$from = (int) request('from');
				$to = (int) request('to');

				$advancedExport = new ExportAdvanced($entryIds, $type, $all, $from, $to);
				echo json_encode([
					'success' => true,
					'exportCode' => $advancedExport->getExportCode(),
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

				/** @var Download $download */
				$download = new Download();
				$download->setEntry($entry->getId());
				$download->setLink($link->getId());
				$download->setIp(AdminCheck::get_ip_address());

				if (AdminCheck::isBanned($entry)) {
					$url = Link::BANNED_URL;
					$download->setComment(Banned::BANNED);
				} else {
					$url = $link->getLink();
				}
				if ($isAddDownload) {
					app('em')->flush($download);
				}

				echo json_encode([
					'link' => $url,
					'success' => true,
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
