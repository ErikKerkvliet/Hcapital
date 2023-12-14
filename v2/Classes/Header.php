<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 8-12-19
	 * Time: 16:14
	 */

	namespace v2\Classes;

	use v2\Manager;
    use v2\Traits\TextHandler;

    class Header
	{
        use TextHandler;

		public function buildContent()
		{
			$file= fopen(Manager::TEMPLATE_FOLDER . 'Header.html', 'r');
			$this->content = fread($file, 10000);
			$url = strpos($_SERVER['HTTP_HOST'], 'hcapital.tk') !== false ? 'http://www.hcapital.tk/' : '/';
			$switch = (AdminCheck::checkForLocal() ? 'http://www.hcapital.tk' :
					'http://localhost') . $_SERVER['REQUEST_URI'];

			if (strpos(request('EntryAction'), 'export') !== false) {
				$switch .= '&EntryAction=import';
			}

			$banner = isset($_SESSION['_18']) && $_SESSION['_18'] == '-' ||
			AdminCheck::checkForLocal() ? $banner = '/images/Anime_Banner.png' : '/images/banner.png';

			$this->placeHolders = [
				'url'           => $url,
				'banner'        => $banner,
				'switch_url'    => $switch,
			];

			$this->cssFiles = array_merge($this->cssFiles, []);

			$this->jsFiles = array_merge($this->jsFiles, []);

			$this->fillPlaceHolders();
			$this->fillIfs();
		}
	}