<?php

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
		 * Manipulate links before buttons are made
		 *
		 * @param array $links
		 * @return array
		 */
		public function manipulateLinks(array $links)
		{
			$links = array_filter($links, function($link) {
				if ($link->getLink() == '-' || $link->getComment() == '-') {
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
				if ((strpos($link->getLink(), '/rapidgator.') !== false) ||
					(strpos($link->getLink(), '/rg.') !== false)) {
					$this->links[$link->getComment()]['rapidgator'][] = $link;
					return;
				}
				if ((strpos($link->getLink(), 'mexashare.') !== false) ||
					(strpos($link->getLink(), 'mx-sh.') !== false) ||
					(strpos($link->getLink(), 'mexa.') !== false)) {
					$this->links[$link->getComment()]['mexashare'][] = $link;
				}
			}, $links);

			$this->setLinkParts();

			$this->setMultipleZeroLinks();

			return $this->links;
		}

		/**
		 * Lets array numbers be filtered by comment.
		 */
		private function setLinkParts()
		{
			array_map(function($links) {
				if (isset($links['rapidgator'])) {
					$this->setParts($links['rapidgator']);
				}

				if (isset($links['mexashare'])) {
					$this->setParts($links['mexashare']);
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
				if (strpos($link->getLink(), '.part') == false && count($links) <= 2) {
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
			if ($this->links && count($this->links['']['rapidgator']) > 1 &&
				$this->links['']['rapidgator'][0]->getPart() == 0) {
				/** @var \v2\Database\Entity\Link $link */
				foreach ($this->links['']['rapidgator'] as $key => &$link) {
					$nr = $key + 1;
					$link->setPart($nr);
				}
			}
		}
	}