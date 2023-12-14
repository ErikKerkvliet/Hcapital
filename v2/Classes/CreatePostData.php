<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 19-4-20
	 * Time: 13:23
	 */

	namespace v2\Classes;

	use HostResolver;
    use v2\Database\Entity\Entry;
    use v2\Database\Entity\Link;
    use v2\Database\Repository\DeveloperRepository;
    use v2\Database\Repository\LinkRepository;
    use v2\Manager;
    use v2\Traits\TextHandler;

    class CreatePostData
	{
        use TextHandler;

		/**
		 * @var Entry|null
		 */
		private $entry = null;

		/**
		 * @var array
		 */
		private $images = [];

		/**
		 * @var array
		 */
		private $links = [];

		/**
		 * @var string
		 */
		private $cover = '';

		/**
		 * @var HostResolver
		 */
		private $hostResolver;

		/**
		 * GameList constructor.
		 * @param $items
		 */
		public function __construct($entry = 0)
		{
			$this->entry = app('em')->find(Entry::class, $entry);
			$this->hostResolver = new HostResolver();

			$file = fopen(Manager::TEMPLATE_FOLDER . 'CreatePostData.html', 'r');
			$this->content = fread($file, 100000);

			$this->cover = request('cover')['name'] ?: false;

			$this->images = request('images') ?: [];

			$this->cssFiles = [
				'PostData',
			];

			$this->jsFiles = [
				'DeveloperList',
			];
		}

		/**
		 * Setup all the page info
		 */
		public function buildContent()
		{
			$this->links();

			$this->placeHolders = [
				'title'         => $this->entry->getTitle(),
				'romanji'       => $this->entry->getRomanji(),
				'released'      => $this->entry->getReleased(),
				'information'   => $this->entry->getInformation(),
				'size'          => $this->entry->getSize(),
				'developers'    => $this->getDevelopers(),
				'cover'         => $this->cover,
			];

			$this->fors = [
				'links'     => $this->getLinks(),
				'images'    => $this->getImageTableData(),
			];

			$this->ifs = [
				'title' => $this->entry->getTitle() ? true : false,
				'romanji'   => $this->entry->getRomanji() ? true : false,
				'cover' => $this->cover,
				'images'    => $this->images ? true : false,
			];

			$this->fillPlaceHolders();
			$this->fillIfs();
			$this->fillFors();
		}

		private function getImageTableData()
		{
			$images = trim($this->images, '\r\n');
			$images = preg_split('/\r\n|[\r\n]/', $images);

			return array_map(function($image, $i) {
				$tr_open = '';
				$tr_close = '';

				if ($i % 5 == 0) {
					$tr_open = '[TR]';
					$tr_close = '[/TR]';
				}

				$splitImage = explode('/', $image);
				unset($splitImage[count($splitImage) - 1]);

				$url = implode('/', $splitImage);
				return [
					'tr'    => $tr_open,
					'url'   => $url,
					'image'   => $image,
					'/tr'   => $tr_close,
				];
			}, $images, array_keys($images));
		}

		private function links()
		{
			/** @var LinkRepository $linkRepository */
			$linkRepository = app('em')->getRepository(Link::class);
			$links = $linkRepository->findBy(['entry' => $this->entry->getId()]);

			array_map(function ($link) {
				if ($link->getLink() == '-') {
					return;
				}

				$host = ucfirst($this->hostResolver->byUrl($link->getLink()));
				$comment = $link->getComment() ?: $host;
				$link = $link->getLink();

				$this->links[$comment][$host][] = $link;

			}, $links);
		}

		private function getLinks()
		{
			$linkArray = [];
			$lastComment = '';
			$filter = ['Rapidgator', 'Mexashare', 'Katfile'];
			foreach($this->links as $comment => $group) {
				foreach ($group as $host => $links) {
					foreach($links as &$link) {
						$link = '[URL]' . $link . '[/URL]';
					}
					$linkArray[] = [
						'comment'   => $comment != $lastComment && ! in_array($comment, $filter) ?
							'[B]' . $comment . '[/B]&#13;&#10;&#13;&#10' : '',
						'host'      => '[B]' . $host . '[/B]',
						'link'      => implode('&#13;&#10', $links),
					];
					$lastComment = $comment;
				}
			}
			return $linkArray;
		}

		private function getDevelopers()
		{
			/** @var DeveloperRepository $developerRepository */
			$developerRepository = app('em')->getRepository(\v2\Database\Entity\Developer::class);

			$developers = array_map(function($developer) {
				return $developer->getName();
			}, $developerRepository->findByEntry($this->entry));

			return implode(' & ', $developers);
		}
	}