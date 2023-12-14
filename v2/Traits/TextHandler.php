<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 8-12-19
	 * Time: 16:27
	 */

	namespace v2\Traits;

	use v2\Classes\AdminCheck;

    trait TextHandler
	{
		/**
		 * @var array
		 */
		public $cssFiles = [];

		/**
		 * @var array
		 */
		public $jsFiles = [];

		/**
		 * @var null
		 */
		public $content = '';

		/**
		 * @var array
		 */
		public $placeHolders = [];

		/**
		 * @var array
		 */
		public $ifs = [];

		/**
		 * @var array
		 */
		public $fors = [];

		public function handleAdminView() {
			if (AdminCheck::checkForAdmin()) {
				$this->content = str_replace('{{admin}}', '', $this->content);
				$this->content = str_replace('{{/admin}}', '', $this->content);

				return;
			}

			preg_match_all('/\{\{admin\}\}(.*?)\{\{\/admin\}\}/s', $this->content, $matches);

			foreach ($matches[0] as $match) {
				$this->content = str_replace($match, '', $this->content);
			}

		}

		public function fillIfs()
		{
			foreach ($this->ifs as $if => $value) {
				$pattern = '/\{if ' . $if . '\}(.*?)\{\/if\}/s';

				preg_match_all($pattern, $this->content, $matches);

				if ($value) {
					$search = '{if ' . $if . '}';
					$this->content = str_replace($search, '', $this->content);
				} else {
					$this->content = str_replace($matches[0], '', $this->content);
				}
			}
			$this->content = str_replace('{/if}', '', $this->content);
		}

		public function fillFors()
		{
			foreach ($this->fors as $for => $values) {
				$text = '';
				$pattern = '/\{for ' . $for . '\}(.*?)\{\/for\}/s';

				preg_match($pattern, $this->content, $match);
				$times = 0;

				foreach ($values as $value) {
					if (count($match) < 2) {
						continue;
					}

					$loopText = $match[1];

					if ($times == 0 && strpos($match[1], '{!') !== false) {
						$pattern = '/\{!(.*?)!\}/s';
						preg_match($pattern, $loopText, $innerMatch);
						$loopText = str_replace([$innerMatch[0]], [''], $loopText);
					}

					if (strpos($loopText, '{times}') !== false) {
						$loopText = str_replace(['{times}'], [$times], $loopText);
					}

					foreach ($value as $key => $val) {
						$placeholder = '{{' . $key . '}}';
						$loopText = str_replace([$placeholder], [$val], $loopText);
					}

					$text .= $loopText;

					$times++;
				}
				if ($match) {
					$this->content = str_replace($match[0], $text, $this->content);
				}
			}
			$this->content = str_replace('{!', '', $this->content);

			$this->content = str_replace('!}', '', $this->content);

			$this->content = str_replace('{/for}', '', $this->content);
		}

		public function fillPlaceHolders()
		{
			foreach ($this->placeHolders as $placeHolder => $value) {
				$search = '{{' . $placeHolder . '}}';
				$this->content = str_replace([$search], [$value], $this->content);
			}
			$this->handleAdminView();
		}

		/**
		 * @return array
		 */
		public function getCss()
		{
			return $this->cssFiles;
		}

		/**
		 * @return array
		 */
		public function getJs()
		{
			return $this->jsFiles;
		}

		private function cleanHtml()
		{
			$re = '/ {2,}/m';
			$this->content = preg_replace($re, '', $this->content);
		}

		/**
		 * @return string
		 */
		public function getContent(): string
		{
			$this->cleanHtml();

			return $this->content;
		}
	}
