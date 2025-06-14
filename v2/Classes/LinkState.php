<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 22-10-23
	 * Time: 18:17
	 */

	namespace v2\Classes;

	loadEnv(str_replace('Classes', '', __DIR__) . '.env');

    use v2\Database\Entity\Link;
    use v2\Manager;
    use v2\Traits\TextHandler;
	use v2\Classes\Validate;

    class LinkState
	{
        use TextHandler;

		private $from = 0;

		private $to = 0;

		private $links = [];

		private $linkString = '';

		private $validator = null;

		private $hosts = [];

		/**
		 * LinkState constructor.
		 * @param null|int $entry
		 */
		public function __construct($from, $to, array $hosts = [])
		{
			$this->hosts = $hosts;
			$this->from = $from;

			$this->to = $to;

			$this->validator = Validator::getValidator();

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
			
			$linkRepository = app('em')->getRepository(Link::class);
			$links = $linkRepository->findBetweenEntry($this->from, $this->to);

			// Use the Validate class to get URL validation results
			$validatedUrls = $this->validator->validateUrlsByLinks($links, $this->hosts);
			/** @var Link $link */
			foreach($links as $link) {
				$url = $link->getLink();
				
				// Check if this URL was validated by the Validate class
				if (isset($validatedUrls[$url])) {
					// Convert the validation result to our expected format
					$status = ($validatedUrls[$url] === 'available') ? 'success' : 'fail';
					// Apply the same state filtering logic as before
					if (request('state') == '2' && $status === 'success' 
						|| request('state') == '1' && $status === 'fail'
					) {
						continue;
					}
					
					$fileStates[$url] = [
						'linkId' => $link->getId(),
						'entryId' => $link->getEntry(true),
						'status' => $status,
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
			}
			$stateIds = array_unique($stateIds);
			$this->linkString = implode(',', $stateIds);
		}
	}