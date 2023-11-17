<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 17-11-23
	 * Time: 21:00
	 */

	namespace v2\Builders;


	class Links2
	{
		/**
		 * @param array $links
		 * @param int $entryId
		 * @return string
		 */
		public function createLinks()
		{
			$this->hostResolver = new HostResolver();
			$linkRepository = app('em')->getRepository(Link::class);
			$this->links = $linkRepository->findBy(['entry' => $this->entry]);

			$linkResolver = new LinkResolver();
			$this->links = $linkResolver->manipulateLinks($this->links);

			if (isset($this->links['text']) && $this->links['text']) {
				$this->buildText();
			}

			unset($this->links['text']);
			foreach ($this->links as $key => $links) {
				if ($key != '') {
					$this->buildLinkHeader($key);
				}
				$this->buildHostLinks($links);
			}
			$this->buildLinkHeader('');

			return $this->html;
		}
	}