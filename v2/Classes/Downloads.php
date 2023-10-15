<?php

	namespace v2\Classes;

	use v2\Database\Entity\Banned;
	use v2\Database\Entity\Download;
	use v2\Database\Repository\DownloadRepository;
	use v2\Manager;

	class Downloads extends TextHandler
	{
		/**
		 * @var null|int
		 */
		private $entry = null;

		/**
		 * @var array
		 */
		private $downloads = [];

		/**
		 * @var array
		 */
		private $banned = [];

		/**
		 * Home constructor.
		 * @param null|int $entry
		 */
		public function __construct(?int $entry = null)
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
				$downloads = $downloadRepository->findBy(['entry' => $this->entry], ['time' => 'DESC']);
			} else {
				$downloads = $downloadRepository->findAll(['time', 'DESC']);
			}

			/** @var Download $download */
			foreach($downloads as $download) {
				$dateTime = $download->getTime();
				$ip = $download->getIp();
				$isBanned = in_array($download->getIp(), $this->banned);

				$this->downloads[] = [
					'tr' => $isBanned ? 'banned_tr' : 'row-color-' . ($row % 2),
					'data_tr' => 'row-color-' . ($row % 2),
					'entryId'=> $download->getEntry(true),
					'linkId' => $download->getLink(true),
					'link' => $download->getLink() ? $download->getLink()->getLink() : '',
					'comment' => $download->getComment(),
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
	}