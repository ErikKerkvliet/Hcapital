<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 14-4-20
	 * Time: 20:00
	 */

	namespace v2\Classes;

	use Imagick as ImagickAlias;

    class ImageHandler
	{
		private $outputDir = '';

		public function __construct($type = 'entry')
		{
			$type = $type == 'entry' ? 'entries' : 'char';

			$this->outputDir = getcwd() . '/entry_images/' . $type;
		}

		public function manipulate($id, $images, $type)
		{	
			$this->outputDir .= '/' . (int) $id;
			foreach ($images as $key => $files) {
				if ($key == 'cover') {
					$image = $files[0];
					$files[] = $image;

					$files[0]['name'] = '_cover_l.jpg';
					$files[1]['name'] = '_cover_m.jpg';
				}
				foreach ($files as $file) {
					$imagick = new \Imagick();

					// Sanitize the file name to remove or replace special characters
					if ($file['name'] != '__img.jpg') {
						$sanitizedFileName = $this->sanitizeFileName($file['name']);
					} else {
						$sanitizedFileName = $file['name'];
					}

					if ($type == 'ova' && $key != 'cover') {
						$output = $this->outputDir . '/' . $key . '/' . $sanitizedFileName;

						copy($file['tmp'], $output);
						chmod($output, 0777);

						continue;
					}

					if (! $file['name']) {
						continue;
					}

					try {
						$imagick->readImage($file['tmp']);
					} catch (\ImagickException $e) {
						echo 'Error while reading: ' . $file['name'];
					}
					$dimensions = $this->getImageDimensions($imagick, $key, $file['name']);

					$imagick->resizeImage($dimensions['width'], $dimensions['height'],
						\Imagick::FILTER_LANCZOS, 1);

					$imagick->setImageFormat('jpg');

					$imageKey = $key === 0 || $key == 'img' || $key == 'image' ? '' : $key . '/';
					$output = $this->outputDir . '/' . $imageKey . $sanitizedFileName;

					$imagick->writeImage($output);

					chmod($output, 0777);

					sleep(0.2);

					$imagick->destroy();
				}
			}
		}

		/**
		 * Sanitize the file name by removing or replacing special characters.
		 *
		 * @param string $fileName The original file name.
		 * @return string The sanitized file name.
		 */
		private function sanitizeFileName($fileName)
		{
			// Replace special characters (e.g., #) with an underscore or remove them
			$sanitized = preg_replace('/[#\?%]/', '_', $fileName);

			// Optionally, you can remove multiple consecutive underscores
			$sanitized = preg_replace('/_+/', '_', $sanitized);

			return $sanitized;
		}

		public function getImageDimensions($image, $key, $name)
		{
			if ($key == 'cover' || $key == 'cg') {
				$maxXY = $key != 'cover' ? 600 : ($name == '_cover_l.jpg' ? 700 : 320);
			} else {
				$maxXY = $key == 'img' ? 300 : 500;
			}

			$imageWidth = $image->getImageWidth();
			$imageHeight = $image->getImageHeight();

			$factorX = $maxXY / $imageWidth;
			$factorY = $maxXY / $imageHeight;
			$factor = 0;

			if ($factorX <= $factorY) {
				$factor = $factorX;
			} else if ($factorX > $factorY)	{
				$factor = $factorY;
			}
			if ($factorX > 1 && $factorY > 1) {
				$factor = 1;
			}

			return [
				'width' => floor($imageWidth * $factor),
				'height' => floor($imageHeight * $factor),
			];
		}
	}