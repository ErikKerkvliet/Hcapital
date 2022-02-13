<?php
	namespace v2\Builders;

	use LinkResolver;
	use v2\Classes\AdminCheck;
	use v2\Database\Entity\Link;
	use v2\Database\Entity\Link2;

	class Links extends Builder
	{
		/**
		 * @var array
		 */
		private $links = [];

		/**
		 * @param array $links
		 * @param int $entryId
		 * @return string
		 */
		public function createLinks()
		{
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

		/**
		 * @param string $header
		 */
		private function buildLinkHeader(string $header)
		{
			if ($header == '-') {
				$this->html .= '<div><br><b>' . $header . '</b></div>';
			} else {
				$this->html .= '<div class="link-header"><b>' . $header . '</b></div>';
			}
		}

		/**
		 * @param array $linksByHost
		 */
		private function buildHostLinks(array $linksByHost)
		{
			$this->html .= '<div class="link-box">';
			foreach ($linksByHost as $host => $links) {
				$host = $this->getHost($links[0]->getLink());
				if ($host) {
					$this->html .= '<div class="host-header"><b>' . ucfirst($host) . ':</b></div>';
				}

				$this->buildLinks($links);
			}
			$this->html .= '</div>';
		}

		/**
		 * @param array $links
		 */
		private function buildLinks(array $links)
		{
			foreach($links as $link) {
				$this->buildLinkButton($link);
			}
		}

		private function buildText() {
			$this->html .= '<div id="text-link-box">';
			$this->html .= '<div id="text-comment">' . $this->links['text'][0]->getComment() . '</div>';

			array_map(function ($link) {
				$this->html .= '<div class="text-link">' . $link->getLink() . '</div>';
			}, $this->links['text']);

			$this->html .= '</div>';
		}

		/**
		 * @param Link $link
		 */
		private function buildLinkButton(Link $link)
		{
			$id = $link->getId();
			$part = $link->getPart();

			$text = $part == '0' ? 'Download' : 'part ' . $part;
			$download = $part == '0' ? 'link-button link-button-download' : 'link-button';

			$url = $link->getLink();
			if ($this->checkIfLink($url)) {
				$this->html .= '<div class="' . $download . '" data-link-id="' . $id . '">' . $text . '</div>';
			} else {
				$this->html .= '<div>' . $url .'</div>';
			}
		}

		/**
		 * @param string $link
		 * @return bool
		 */
		private function checkIfLink(string $link): bool
		{
			return strpos($link, 'http://') !== false ||
				strpos($link, 'https://') !== false;
		}

		/**
		 * @param string $link
		 * @return string
		 */
		private function getHost($link)
		{
			if ((strpos($link, 'rapidgator.net') !== false) ||
				(strpos($link, 'rg.to/') !== false)) {
				$host = 'Rapidgator';
			} else if (strpos($link, 'mexashare.com') != false) {
				$host = 'Mexashare';
			} else if (strpos($link, 'mx-sh.net') != false) {
				$host = 'Mexashare';
			} else if (strpos($link, 'mexa.sh') != false) {
				$host = 'Mexashare';
			} else if (strpos($link, 'bigfile.to') != false) {
				$host = 'Bigfile';
			} else if (strpos($link, 'katfile.com') != false) {
				$host = 'Mexashare';
			} else {
				$host = '';
			}

			return $host;
		}
	}
