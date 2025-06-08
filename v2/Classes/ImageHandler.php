<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 14-4-20
	 * Time: 20:00
	 */

	namespace v2\Classes;

	// Assuming Imagick is available. No ImagickAlias needed if \Imagick is used directly.

    class ImageHandler
	{
		private $outputDir = '';
		private $instanceType = ''; // To store 'entries' or 'char' from constructor

		public function __construct($type = 'entry')
		{
			// Determine instance type for directory structure logic
			$this->instanceType = ($type == 'entry') ? 'entries' : 'char';
			// Set base output directory based on the determined instance type
			$this->outputDir = getcwd() . '/entry_images/' . $this->instanceType;
		}

		public function manipulate($id, $images, $type) // $type here is specific to manipulate (e.g., 'ova')
		{
			// Append ID to the base output directory
			$baseOutputDirWithId = $this->outputDir . '/' . (int) $id;

			// 1. Ensure the base output directory for this ID exists (e.g., .../entries/{id} or .../char/{id})
			if (!is_dir($baseOutputDirWithId)) {
				if (!mkdir($baseOutputDirWithId, 0777, true) && !is_dir($baseOutputDirWithId)) {
					echo 'Error: Could not create base directory: ' . $baseOutputDirWithId;
					return;
				}
			}

			foreach ($images as $key => $inputFiles) { // Renamed $files to $inputFiles to avoid confusion with $file
				$filesToProcess = $inputFiles; // Start with the input files for this key

				if ($key == 'cover') {
					// Special handling for 'cover' images to create _l.jpg and _m.jpg versions
					if (isset($inputFiles[0]) && is_array($inputFiles[0])) {
						$originalCoverInfo = $inputFiles[0];
						
						$coverL = $originalCoverInfo;
						$coverL['name'] = '_cover_l.jpg';
						
						$coverM = $originalCoverInfo; // Create a distinct copy for the medium version
						$coverM['name'] = '_cover_m.jpg';
						
						$filesToProcess = [$coverL, $coverM]; // These two will be processed
					} else {
						// If $inputFiles[0] for 'cover' is not set or invalid, process an empty array
						$filesToProcess = []; 
					}
				}

				foreach ($filesToProcess as $file) {
					if (empty($file['name']) || empty($file['tmp'])) {
						continue;
					}

					$imagick = new \Imagick();

					// Sanitize the file name
					if ($file['name'] != '__img.jpg') {
						$sanitizedFileName = $this->sanitizeFileName($file['name']);
					} else {
						$sanitizedFileName = $file['name'];
					}

					// Determine the specific output directory for the current file
					$currentFileOutputDir = $baseOutputDirWithId; // Default: .../{instanceType}/{id}

					if ($this->instanceType === 'entries') {
						if ($key === 'cover') {
							$currentFileOutputDir .= '/cover'; // Path: .../entries/{id}/cover
						} else if ($key === 'cg') {
							$currentFileOutputDir .= '/cg';    // Path: .../entries/{id}/cg
						} else {
							// For 'entries' type, but key is not 'cover' or 'cg':
							// Apply original sub-directory logic relative to $baseOutputDirWithId
							// $type is the parameter from manipulate() method (e.g., 'ova')
							if ($type == 'ova' && $key != 'cover') { // 'cover' already handled
								$currentFileOutputDir .= '/' . $key;
							} else {
								// General case, not 'ova'. Check if $key should form a subdirectory.
								// Original: !($key === 0 || $key == 'img' || $key == 'image' || $key == 'cover')
								// 'cover' and 'cg' are handled. Numeric keys and 'img'/'image' don't form subdirs.
								if (!is_numeric($key) && !in_array((string)$key, ['img', 'image'], true)) {
									$currentFileOutputDir .= '/' . $key;
								}
							}
						}
					} else if ($this->instanceType === 'char') {
						// For 'char' type, all files go directly into $baseOutputDirWithId (e.g., .../char/{id}/)
						// No additional subdirectories based on $key or manipulate's $type.
						// $currentFileOutputDir remains $baseOutputDirWithId.
					}
					
					$outputFilePath = $currentFileOutputDir . '/' . $sanitizedFileName;

					// 2. Ensure the specific subdirectory for this file exists
					if (!is_dir($currentFileOutputDir)) {
						if (!mkdir($currentFileOutputDir, 0777, true) && !is_dir($currentFileOutputDir)) {
							echo 'Error: Could not create directory: ' . $currentFileOutputDir . ' for file ' . $sanitizedFileName;
							$imagick->destroy();
							continue;
						}
					}

					// Processing logic (copy for 'ova' types (if key is not cover), imagick for others)
					// Note: $type is from manipulate() method parameters
					if ($type == 'ova' && $key != 'cover') {
						if (copy($file['tmp'], $outputFilePath)) {
							chmod($outputFilePath, 0777);
						} else {
							echo 'Error: Could not copy file to: ' . $outputFilePath;
						}
						$imagick->destroy(); // Clean up imagick instance
						continue; // Move to the next file
					}

					// Imagick processing for non-'ova' (or 'ova' covers)
					try {
						$imagick->readImage($file['tmp']);
					} catch (\ImagickException $e) {
						echo 'Error while reading: ' . $file['name'] . ' - ' . $e->getMessage();
						$imagick->destroy();
						continue;
					}
					
					$dimensions = $this->getImageDimensions($imagick, $key, $file['name']);

					if ($dimensions['width'] > 0 && $dimensions['height'] > 0) {
						$imagick->resizeImage($dimensions['width'], $dimensions['height'],
							\Imagick::FILTER_LANCZOS, 1);
					}

					$imagick->setImageFormat('jpg');
					$imagick->setImageCompressionQuality(85);
					
					// Corrected image writing logic (removed dd())
					if ($imagick->writeImage($outputFilePath)) {
						chmod($outputFilePath, 0777);
					} else {
						echo 'Error: Could not write image to: ' . $outputFilePath;
					}

					sleep(0.2); // Original sleep call
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
			// Replace special characters (e.g., #, ?, %) with an underscore
			$sanitized = preg_replace('/[#\?%]/', '_', $fileName);
			// Replace multiple consecutive underscores with a single one
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

			// Prevent division by zero if image dimensions are invalid
			if ($imageWidth == 0 || $imageHeight == 0) {
			    return ['width' => 0, 'height' => 0]; // Or handle error appropriately
			}

			$factorX = $maxXY / $imageWidth;
			$factorY = $maxXY / $imageHeight;
			$factor = 0;

			if ($factorX <= $factorY) {
				$factor = $factorX;
			} else { // $factorX > $factorY
				$factor = $factorY;
			}
			// If image is smaller than maxXY, don't scale up unless desired.
            // Current logic: if factor > 1 (meaning image is smaller than maxXY), factor becomes 1 (no upscale).
			if ($factor > 1) { // Corrected logic: was ($factorX > 1 && $factorY > 1)
				$factor = 1;
			}

			return [
				'width' => floor($imageWidth * $factor),
				'height' => floor($imageHeight * $factor),
			];
		}
	}