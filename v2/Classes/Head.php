<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 8-12-19
	 * Time: 21:28
	 */

	namespace v2\Classes;

	use v2\Manager;
    use v2\Traits\TextHandler;

    class Head
	{
        use TextHandler;

		public function __construct(){}

		public function buildContent()
		{
			$file = fopen(Manager::TEMPLATE_FOLDER . 'Head.html', 'r');
			$this->content = fread($file, 100000);

			$this->placeHolders = [
				'css'   => $this->generateCSS(),
				'js'    => $this->generateJs(),
			];

			$this->fillPlaceHolders();
		}

		public function fillJsCss($cssFiles, $jsFiles) {
			if (AdminCheck::checkForAdmin()) {
				$cssFiles[] = 'Admin';
				$jsFiles[] = 'Admin';
			}

			$this->cssFiles = $cssFiles;

			$this->jsFiles = $jsFiles;
		}


		private function generateCSS(): string
		{
			$version = Manager::CSS_JS_VERSION;

			$this->cssFiles[] = 'Global';
			$this->cssFiles[] = 'Header';

			$this->cssFiles = array_unique($this->cssFiles);
			$css = '';
			foreach ($this->cssFiles as $cssFile) {
				$css .= '<link rel="stylesheet" href="/v2/css/' . $cssFile . '.css?v=' . $version . '" type="text/css" media="screen" title="no title" charset="UTF-8"/>';
			}

			return $css;
		}

		private function generateJs(): string
		{
			$version = Manager::CSS_JS_VERSION;

			$this->jsFiles[] = '3.2.1.jquery.min';
			$this->jsFiles[] = 'Global';
			$this->jsFiles[] = 'Header';

			$this->jsFiles = array_unique($this->jsFiles);
			$js  = '';
			foreach ($this->jsFiles as $jsFile) {
				$js .= '<script type="text/javascript" src="v2/js/' . $jsFile . '.js?v=' . $version . '"></script>';
			}
			return $js;
		}
	}