<?php

	namespace v2\Classes;

	use v2\Database\Entity\Banned;
    use v2\Database\Entity\Download;
    use v2\Database\Repository\DownloadRepository;
    use v2\Manager;
    use v2\Traits\TextHandler;
	use v2\Classes\Validator;
	use v2\Database\Entity\Host;

    class Downloads
	{
        use TextHandler;

		/**
		 * @var null|int
		 */
		private $entry = null;

		/**
		 * @var bool
		 */
		private $validate = false;

		/**
		 * @var array
		 */
		private $downloads = [];

		/**
		 * @var array
		 */
		private $urls = [];

		/**
		 * @var array
		 */
		private $banned = [];

		/**
		 * @var string
		 */
		private $comment = '';

		/**
		 * Home constructor.
		 * @param null|int $entry
		 */
		public function __construct(?int $entry = null, bool $validate = false)
		{
			$file = fopen(Manager::TEMPLATE_FOLDER . 'Downloads.html', 'r');
			$this->content = fread($file, 10000);

			$this->cssFiles = [
				'Home',
				'Downloads',
			];

			$this->jsFiles = [
				'Home',
				'Downloads',
			];

			$this->validate = $validate;
			$this->entry = $entry;
		}

		public function buildContent()
		{
			$admin = AdminCheck::checkForAdmin();

			$this->ifs = [
				'online'   => $admin && ! AdminCheck::checkForLocal(),
				'local'    => $admin && AdminCheck::checkForLocal(),
			];

			$this->getBanned();
			$this->getDownloads();

			$this->fors = [
				'downloads' => $this->downloads,
			];
			$this->fillIfs();
			$this->fillFors();
			$this->fillPlaceHolders();
		}

		private function getDownloads()
		{
			/** @var DownloadRepository $downloadRepository */
			$downloadRepository = app('em')->getRepository(Download::class);
			$row = 0;

			if ($this->entry) {
				$downloads = $downloadRepository->findBy(['entry' => $this->entry], ['created' => 'DESC']);
			} else {
				$downloads = $downloadRepository->findAll(['created', 'DESC'], [0, 1000]);
			}

			$keys = [];
			$downloads = array_filter($downloads, function ($download) use (&$keys) {
				$key = $download->getEntry(true) . '-' . $download->getLink(true) . '-' . $download->getIp();
				if (!isset($keys[$key])) { 
					$keys[$key] = true;
					return true;
				}
			});

			if ($this->validate) {
				$validator = Validator::getValidator();
				$this->urls = $validator->validateUrlsByDownloads($downloads, Host::HOSTS);
			}

			/** @var Download $download */
			foreach($downloads as $download) {
				$dateTime = $download->getCreated();
				$ip = $download->getIp();
				$isBanned = in_array($download->getIp(), $this->banned);
				// $this->comment = $download->getComment();
				$link = $download->getLink();
				if (is_null($link)) {
					continue;
				}
				$this->downloads[] = [
					'data_tr' => 'row-color-' . ($row % 2),
					'entryId'=> $download->getEntry(true),
					'linkId' => $link->getId(),
					'link' => $link->getLink(),
					'tr' => $this->getTr($download, $row),
					'comment' => $this->comment,
					'ip' => $ip,
					'ban' => $isBanned ? 'Unban' : 'Ban',
					'time' => date_format(new \DateTime($dateTime), 'd-m-Y | H:i'),
				];
				$row++;
			};
		}

		private function getBanned()
		{
			$bannedRepository = app('em')->getRepository(Banned::class);

			/** @var Banned $banned */
			foreach ($bannedRepository->findAll() as $banned) {
				$this->banned[] = $banned->getIp();
			}
		}

		/**
		 * Returns the row class based on the url status.
		 *
		 * @param Download $download
		 * @param int $row
		 * @return string
		 */
		private function getTr(Download $download, $row): string
		{
			$url = $download->getLink() ? $download->getLink()->getLink() : '-';
			$this->comment = '';
			
			if(in_array($download->getIp(), $this->banned)) {
				$this->comment = 'Banned';
				return 'tr_banned';
			}

			if ($this->validate && isset($this->urls[$url]) && $this->urls[$url] == 'unavailable') {
				$this->comment = 'Link unavailable';
				return 'tr_unavailable';
			}

			if ($download->getComment()) {
				$this->comment = $download->getComment();
				return 'tr_comment';
			}

			return 'row-color-' . $row % 2;
		}
	}