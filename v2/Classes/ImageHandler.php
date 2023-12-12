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

					if ($type == 'ova' && $key != 'cover') {
						$output = $this->outputDir . '/' . $key . '/' . $file['name'];

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

//					$input = $file['tmp'];
//					$output = $this->outputDir . '/' . $file['name'];

//					shell_exec(sprintf('convert "%s" -crop %dx%d+0+0 "%s"',
//						$input, $dimensions['width'], $dimensions['height'], $output));

					$imagick->resizeImage($dimensions['width'], $dimensions['height'],
						\Imagick::FILTER_LANCZOS, 1);

					$imagick->setImageFormat('jpg');

					$imageKey = $key == 'img' || $key == 'image' ? '' : $key . '/';
					$output = $this->outputDir . '/' . $imageKey . $file['name'];

					$imagick->writeImage($output);

					chmod($output, 0777);

					sleep(0.2);

					$imagick->destroy();
				}
			}
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