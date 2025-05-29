<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 8-12-19
	 * Time: 12:40
	 */

	namespace v2\Classes;

use v2\Database\Entity\Character;
    use v2\Database\Entity\Developer;
    use v2\Database\Entity\Entry;
    use v2\Database\Entity\Thread;
	use v2\Factories\ThreadFactory;
	use v2\Manager;
    use v2\Traits\TextHandler;

    class Main
	{
        use TextHandler;

		/**
		 * Main constructor.
		 */
		public function buildContent()
		{
			require_once('Head.php');
			require_once('Header.php');
			require_once('Borders.php');
			require_once('Footer.php');

			require_once('EntryInfo.php');

			$file = fopen(Manager::TEMPLATE_FOLDER . 'Main.html', 'r');
			$this->content = fread($file, 10000);

			$head = new Head();
			$header = new Header();
			$borders = new Borders();
			$footer = new Footer();

			$list = (request('l') || request('s'));

			$content = null;

			if (! request('v')) {
				$content = new Home();
			} else if (request('u')) {
				$content = new Upcoming();
			} else if (($id = request('id')) && ! request('_cid')) {
				if ($entry = app('em')->find(Entry::class, $id)) {
					$content = new EntryInfo($entry);
				}
			} else if ($cid = request('cid')) {
				if($character = app('em')->find(Character::class, $cid)) {
					$content = new \v2\Classes\Character($character);
				}
			} else if ($did = request('did')) {
				if ($developer = app('em')->find(Developer::class, $did)) {
					$content = new \v2\Classes\Developer($developer);
				}
			} else if (request('t')) {
				$content = new ListMain();
			} else if ((request('l') && request('t')) || request('by')) {

				$content = new ListMain();
			} else if (request('l') === 'a') {
				$content = new ListMain();
			} else if ($list) {
				$content = new ListMain();
			} else if (request('action') == 'di') {
				$entry = request('entry') ?: null;
				$content = new Downloads($entry);
			} else if (request('action') == 'lv') {
				if (AdminCheck::checkForAdmin()) {
					$from = (int)request('from');
					$to = (int)request('to');

					$content = new LinkState($from, $to);
				} else {
					$content = new Home();
				}
			} else if (request('action') === 'exportAdvanced') {
				if (AdminCheck::checkForAdmin()) {
					$entryIds = explode(',', request('ids'));
					$content = new ExportAdvanced($entryIds);
				} else {
					$content = new Home();
				}
			} else if (request('getPost')) {
				if (AdminCheck::checkForAdmin()) {
					$content = new CreatePostData(request('getPost'));
				} else {
					$content = new Home();
				}
			} else if (($id = request('_id'))) {
				if (AdminCheck::checkForAdmin()) {
					$entry = app('em')->find(Entry::class, $id);
					$content = new InsertEdit($entry);
				} else {
					$content = new Home();
				}
			} else if (($cid = request('_cid')) && AdminCheck::checkForAdmin()) {
				$character = app('em')->find(Character::class, $cid);

				$content = new InsertEditCharacter($character, request('id'));
			} else if (($did = request('_did')) && AdminCheck::checkForAdmin()) {
				$developer = app('em')->find(Developer::class, $did);

				$content = new InsertEditDeveloper($developer);
			} else if (request('random') === 'true') {
				$content = new RandomEntries();
			} else if ($action = request('EntryAction')) {
				if ($action == 'insert')
				{
					$content = new InsertEdit(null, true);
				} else if ($action == 'edit') {
					$entry = app('em')->find(Entry::class, request('entryId'));

					$content = new InsertEdit($entry);
				} else if ($action == 'editCharacter') {
					$entry = app('em')->find(Entry::class, request('entryId'));
					$content = new InsertEditCharacter($entry, request('id'));
				} else if ($action == 'insertCharacter') {
					$content = new InsertEditCharacter(null, request('entryId'));
				} else if ($action == 'export') {
					$entry = app('em')->find(Entry::class, request('entryId'));
					$content = new Export($entry, 'entry');
				} else if ($action == 'exportAll') {
					$entry = app('em')->find(Entry::class, request('entryId'));
					$content = new Export($entry, 'entry', true);
				} else if ($action === 'exportLinks') {
					$entry = app('em')->find(Entry::class, request('entryId'));
					$content = new Export($entry, 'link');
				} else if ($action === 'exportLinksAll') {
					$entry = app('em')->find(Entry::class, request('entryId'));
					$content = new Export($entry, 'link', true);
				} else if ($action == 'import') {
					$content = new Import();
				} else if ($action == 'importEntry') {
					$entryAction = new EntryActions();
					$entryAction->doAction('import');
					return;
				} else if ($action == 'moveTo') {
					$entry = app('em')->find(Entry::class, (int)request('entryId'));

					$entry->setTimeType(request('moveTo'));

					if (request('entryType') == 'app') {
						$entry->setType('game');
					}

					app('em')->update($entry);
					app('em')->flush();

					echo json_encode([
						'success' => true,
					]);
					die();
				} else if ($action == 'updateSharingUrl') {
					$threadRepository = app('em')->getRepository(Thread::class);
					$threadFactory = new ThreadFactory();

					$thread = $threadRepository->findOneBy([
						'entry' => request('entry', 0),
						'number' => request('number', 0)]
					);

					$data = request('all');
					$remove = ['v', 'EntryAction'];

					foreach ($remove as $key) {
						unset($data[$key]);
					}

					if ($thread) {
						$threadFactory->update($thread, $data);
						app('em')->update($thread);
						app('em')->flush();
					} else {
						$thread = $threadFactory->create($data);
						app('em')->flush($thread);
					}
					
					echo json_encode([
						'success' => true,
					]);
					die();
				} else if ($action === 'updateThread' && ($entryId = request('entryId'))) {
					$command = 'python /home/erik/PycharmProjects/CreateThread/source/Main.py update ' . $entryId;
					echo json_encode([
						'command' => $command,
						'success' => true,
					]);
					die();
				}
			} else {
				return;
			}

			$this->setupClass($head);
			$this->setupClass($header);
			$this->setupClass($borders);

			if ($content) {
				$this->setupClass($content);
			}
			$this->setupClass($footer);

			$head->fillJsCss($this->cssFiles, $this->jsFiles);

			$head->buildContent();

			$this->placeHolders = [
				'head'      => $head->getContent(),
				'header'    => $header->getContent(),
				'borders'   => $borders->getContent(),
				'content'   => $content ? $content->getContent() : '',
				'footer'    => $footer->getContent(),
			];

			$this->fillPlaceHolders();

			echo $this->content;
		}

		/**
		 * Build class and get the css/js files they contain.
		 * @param $class
		 */
		private function setupClass($class) {
			$class->buildContent();

			$this->cssFiles = array_merge($this->cssFiles, $class->getCss());
			$this->jsFiles = array_merge($this->jsFiles, $class->getJs());
		}
	}
