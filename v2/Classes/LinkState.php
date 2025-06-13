<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 22-10-23
	 * Time: 18:17
	 */

	namespace v2\Classes;

	loadEnv(str_replace('Classes', '', __DIR__) . '.env');

	use v2\ClientException;
    use v2\Database\Entity\Link;
    use v2\Manager;
    use v2\RapidgatorClient;
    use v2\Traits\TextHandler;
	use v2\Classes\Validate;

    class LinkState
	{
        use TextHandler;

		private $from = 0;

		private $to = 0;

		private $links = [];

		private $linkString = '';

		private $validate = null;

		/**
		 * LinkState constructor.
		 * @param null|int $entry
		 */
		public function __construct($from, $to)
		{
			$this->from = $from;

			$this->to = $to;

			$this->validate = new Validate();

			$file = fopen(Manager::TEMPLATE_FOLDER . 'LinkState.html', 'r');
			$this->content = fread($file, 10000);
			$this->cssFiles = [
				'Home',
				'LinkState',
			];

			$this->jsFiles = [
				'LinkState'
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

			$this->placeHolders = [
				'linkString' => $this->linkString,
			];

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
			$client = new RapidgatorClient(getenv('RAPIDGATOR_USERNAME'), getenv('RAPIDGATOR_PASSWORD'), null);

			$linkRepository = app('em')->getRepository(Link::class);
			$links = $linkRepository->findBetweenEntry($this->from, $this->to);

			// $urls = $this->validate->validateUrlsByLinks($links);

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
					if (request('state') == '1' && $response->status !== 200 
						|| request('state') == '2' && $response->status === 200
					) {
						continue;
					}
					$fileStates[$url] = [
						'linkId' => $link->getId(),
						'entryId' => $link->getEntry(true),
						'status' => ($response->status === 200 ? 'success' : 'fail'),
					];
				}
			}

			$row = 0;
			$stateIds = [];
			foreach($fileStates as $key => $state) {
				$stateIds[] = $state['entryId'];
				$this->links[] = [
					'tr' => 'row-color-' . ($row % 2),
					'link' => $state['linkId'],
					'url' => $key,
					'entry' => $state['entryId'],
					'status' => $state['status'],
				];
				$row++;
			};
			$stateIds = array_unique($stateIds);
			$this->linkString = implode(',', $stateIds);
		}
	}