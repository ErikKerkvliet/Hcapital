<?php

	namespace v2\Resolvers;

	use v2\Resolvers\HostResolver;
	use v2\Database\Entity\Host;

	/**
	 * Class LinkResolver
	 */
	class LinkResolver
	{
		/**
		 * @var array
		 */
		private $links = [];

		/**
		 * @var int
		 */
		private $linkCount = 1;

		/**
		 * @var HostResolver
		 */
		private $hostResolver;

		public function byLinksAndUrl(array $links, string $url)
		{
			$this->hostResolver = new HostResolver();

			$path = parse_url($url, PHP_URL_PATH);
			$filenameWithHtml = basename($path);

			// Remove the ".html" extension
			$filename = preg_replace('/\.html$/', '', $filenameWithHtml);

			$chars_to_remove = [' ', '-', '_'];
			$filename = str_replace($chars_to_remove, '', $filename);

			$host = $this->hostResolver->byUrl($url);

			$filtered = array_filter($links, function($link) use ($host, $filename, $chars_to_remove) {
				if ($host === $this->hostResolver->byUrl(($url = $link->getUrl()))) {
					$url = str_replace($chars_to_remove, '', $url);
					return strpos($url, $filename) !== false;
				}
			});

			natsort($filtered);
			return reset($filtered);
		}

		/**
		 * Manipulate links before buttons are made
		 *
		 * @param $links
		 * @return array
		 */
		public function manipulateLinks(array $links)
		{
			$this->hostResolver = new HostResolver();
			$links = array_filter($links, function($link) {
				if ($link->getUrl() == '-' || $link->getComment() == '-') {
					return false;
				}
				return true;
			});
			array_map(function($link) {
				if (substr($link->getComment(), 0, 2) == '<<') {
					$link->setComment(substr($link->getComment(),2));
					$this->links['text'][] = $link;
					return;
				}
				$host = $this->hostResolver->byUrl($link->getUrl());
				$this->links[$link->getComment()][$host][] = $link;
			}, $links);
			
			unset($this->links[''][HOST::HOST_FIKPER]);
			unset($this->links[''][HOST::HOST_DDOWNLOAD]);
			unset($this->links[''][HOST::HOST_ROSEFILE]);

			$this->setLinkParts();

			$this->setMultipleZeroLinks();

			$this->orderLinksCorrectly($this->links);

			return $this->links;
		}

		/**
		 * Lets array numbers be filtered by comment.
		 */
		private function setLinkParts()
		{
			array_map(function($links) {
				foreach (Host::HOSTS as $host) {
					if (isset($links[$host])) {
						$this->setParts($links[$host]);
					}
				}
			}, $this->links);
		}

		/**
		 * Set the part number of the link.
		 *
		 * @param array $links
		 */
		private function setParts(array $links)
		{
			array_map(function($link) use ($links) {
				if (strpos($link->getUrl(), '.part') == false && count($links) <= 2) {
					return;
				}
				$link->setPart($this->linkCount);
				$this->linkCount++;
			}, $links);

			$this->linkCount = 1;
		}

		/**
		 * Set correct link numbers if there are multiple zero parts,
		 */
		private function setMultipleZeroLinks()
		{
			if (isset($this->links[''][Host::HOST_RAPIDGATOR])
				&& count($this->links[''][Host::HOST_RAPIDGATOR]) > 1 &&
				$this->links[''][Host::HOST_RAPIDGATOR][0]->getPart() == 0
			) {
				/** @var \v2\Database\Entity\Link $link */
				foreach ($this->links[''][Host::HOST_RAPIDGATOR] as $key => &$link) {
					$nr = $key + 1;
					$link->setPart($nr);
				}
			}
		}

		private function orderLinksCorrectly($linkSet)
		{
			foreach ($linkSet as $key => $links) {
				foreach (Host::HOSTS as $host) {
					if (! in_array($key, Host::HOSTS)) {
						$this->links[$key] = $this->orderLinksCorrectly($links);
					}
				}
			}

			if (! is_array($linkSet)) {
				return ['' => [$linkSet]];
			}
			
			$keySet = array_keys($linkSet);
			if ($linkSet && $keySet[0] !== Host::HOST_RAPIDGATOR) {
				return array_reverse($linkSet);
			}
			return $linkSet;
		}
	}