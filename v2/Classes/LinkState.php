<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 22-10-23
	 * Time: 18:17
	 */

	namespace v2\Classes;

	use v2\ClientException;
	use v2\Database\Entity\Link;
	use v2\Manager;
	use v2\RapidgatorClient;

	class LinkState extends TextHandler
	{
		private $from = 0;

		private $to = 0;

		private $links = [];

		/**
		 * Home constructor.
		 * @param null|int $entry
		 */
		public function __construct($from, $to)
		{
			$this->from = $from;

			$this->to = $to;

			$file = fopen(Manager::TEMPLATE_FOLDER . 'LinkState.html', 'r');
			$this->content = fread($file, 10000);
			$this->cssFiles = [
				'Home',
				'LinkState',
			];

			$this->jsFiles = [

			];
		}

		public function buildContent()
		{
			$admin = AdminCheck::checkForAdmin();

			$this->ifs = [
				'online'   => $admin && ! AdminCheck::checkForLocal(),
				'local'    => $admin && AdminCheck::checkForLocal(),
			];

			if ($this->to) {
				$this->getLinkData();
			}

			$this->fors = [
				'links' => $this->links,
			];
			$this->fillIfs();
			$this->fillFors();
			$this->fillPlaceHolders();
		}

		private function getLinkData()
		{
			$fileStates = [];
			$client = new RapidgatorClient('public.rapidgator@gmail.com', '1I^uDckm$d92PEaE*1Z');

			$linkRepository = app('em')->getRepository(Link::class);
			$links = $linkRepository->findBetweenEntry($this->from, $this->to);
			/** @var Link $link */
			foreach($links as $link) {
				$url = $link->getLink();
				if (strpos($url,   '://rapidgator') !== false || strpos($url, '://rg.to') !== false) {
					$fileId = explode('/', explode('file', $url)[1])[1];
					try {
						$response = $client->getFileDetails($fileId);
					} catch (ClientException $e) {
						dd($e);
					}
					$fileStates[$url] = [
						'linkId' => $link->getId(),
						'entryId' => $link->getEntry(true),
						'status' => ($response->status === 200 ? 'success' : 'fail'),
					];
				}
			}

			$row = 0;
			foreach($fileStates as $key => $state) {
				$this->links[] = [
					'tr' => 'row-color-' . ($row % 2),
					'link' => $state['linkId'],
					'url' => $key,
					'entry' => $state['entryId'],
					'status' => $state['status'],
				];
				$row++;
			};
		}
	}