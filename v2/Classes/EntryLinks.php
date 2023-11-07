<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 21-12-19
	 * Time: 2:22
	 */

	namespace v2\Classes;

	use HostResolver;
	use v2\Database\Entity\Link;
	use v2\Manager;

	class EntryLinks extends TextHandler
	{
		private $entry = null;

		private $links = [];

		private $headers = [];

		private $comments = [];

		private $hostResolver;

		public function __construct($entry)
		{
			$this->entry = $entry;

			$this->hostResolver = new HostResolver();
			$linkRepository = app('em')->getRepository(Link::class);

			$this->links = $linkRepository->findBy(['entry' => $this->entry]);
		}

		public function buildContent()
		{
			$file = fopen(Manager::TEMPLATE_FOLDER . 'EntryLinks.html', 'r');
			$this->content = fread($file, 100000);

			$this->fors = [
				'links'     => $this->getLinks(),
			];

			$this->ifs = [
				'comment' => 'true',
			];

			$this->fillFors();
			$this->fillIfs();
			$this->fillPlaceHolders();
		}

		private function buildLinks()
		{
			$lastHeader = '';
			/** @var Link $link */
			foreach ($this->links as $link) {
				$comment = $link->getComment();
				$this->headers[] = $lastHeader != $comment ? $comment :
					(! $comment ? ucfirst($this->hostResolver->byUrl($link)) : '');

				$this->comments[$comment] += 1;

				$this->links[] = $link->getLink();
			}
		}

		private function getButtonText($link, $nr = 0)
		{
			return $nr;
		}

		private function getLinks() {
			return array_map(function ($link) {

			}, $this->links);
		}
	}

