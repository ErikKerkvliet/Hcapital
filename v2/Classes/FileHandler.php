<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 15-12-19
	 * Time: 23:30
	 */

	namespace v2\Classes;

	class FileHandler
	{
		CONST IMAGE_ROOT = './';

		CONST ENTRY_IMAGES_ROOT = '../../../entry_images';

		CONST CSS_ROOT = './css';

		CONST JS_ROOT = './js';

		public function getEntryImages($entryId)
		{
			$src = AdminCheck::checkForLocal() ? 'Hcapital' : 'html';
			$folderPath = '/var/www/' . $src . '/entry_images/entries/' . $entryId . '/cg/';

			if (file_exists($folderPath)) {
				$files = scandir($folderPath);

				return array_filter($files, function ($file) use ($folderPath) {
					if (is_file($folderPath . $file)) {
						return true;
					};
					return false;
				});
			}
			return [];
		}
	}
?>