<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 12-4-20
	 * Time: 17:59
	 */

	namespace v2\Classes;

	use \v2\Database\Entity\Character;
	use v2\Database\Entity\Entry;
	use v2\Database\Entity\EntryCharacter;

	class CharacterActions
	{
		const CHARACTER = 'character';

		private $imageHandler = null;

		private $entry = null;

		private $id = false;
		private $insert = false;

		private $name = '';
		private $romanji = '';
		private $age = 0;
		private $gender = 'female';
		private $height = 0;
		private $weight = 0;
		private $cup = '';
		private $bust = 0;
		private $hips = 0;
		private $waist = 0;

		private $thumbnail;
		private $images = [];
		private $imagePath;
		private $outputDir;

		/** @var Character */
		private $character;

		private $characterId = 0;

		public function __construct($insert = false, $id = 0)
		{
            return;
			$this->imageHandler = new ImageHandler('char');

			$this->insert = $insert;

			if ($insert) {
				$this->character = new Character();
				$this->entry = app('em')->find(Entry::class, $id);
                if (($this->characterId = request('existing'))) {
                    $this->insertEntryCharacter();
                    return;
                }
			} else {
				$this->characterId = $id;
				$this->character = app('em')->find(Character::class, $id);
			}

			$this->id = $id ?: (request('character-id') ?: request('id'));

			$this->name = request('name') ?: '';
			$this->romanji = request('romanji') ?: '';
			$this->age = request('age') ?: '';
			$this->gender = request('gender') ?: '';
			$this->height = request('height') ?: '';
			$this->weight = request('weight') ?: '';
			$this->cup = request('cup') ?: '';
			$this->bust = request('bust') ?: '';
			$this->hips = request('hips') ?: '';
			$this->waist = request('waist') ?: '';
			$this->thumbnail = request('thumbnail');
			$this->images = request('images') ?: '';

			if ($names = request('names')) {
				$names = trim($names);
				$chars = $this->mb_str_split($names);

				foreach ($chars as $key => $char) {
					if ($this->isJapanese($char)) {
						$slice = $key;

						$this->name = substr($names, $slice);
						$this->name = trim($this->name);

						$bloodArray = [' a', ' b', 'ab', ' o'];
						if (in_array(strtolower(substr($this->name, -2)), $bloodArray)) {
							$this->name = substr($this->name, 0, -2);
							$this->name = trim($this->name);
						}

						$this->romanji = substr($names, 0, $slice);

						break;
					}
				}
			}

			if ($measurements = request('measurements')) {
				$measurements = explode(',', $measurements);

				foreach ($measurements as $measurement) {
					if (strpos($measurement, 'Height:') !== false) {
						$measurement = preg_replace('/[^0-9.]/', '', $measurement);

						$this->height = trim($measurement);
					} else if (strpos($measurement, 'Weight:') !== false) {
						$measurement = preg_replace('/[^0-9.]/', '', $measurement);

						$this->weight = trim($measurement);
					} else if (strpos($measurement, 'Bust-Waist-Hips:') !== false) {
						$measurement = str_replace('Bust-Waist-Hips:', '', $measurement);
						$measurement = str_replace('cm', '', $measurement);

						$measurement = trim($measurement);

						$measurementsArray = explode('-', $measurement);

						if (count($measurementsArray) == 3) {
							$this->bust = $measurementsArray[0];
							$this->waist = $measurementsArray[1];
							$this->hips = $measurementsArray[2];
						}
					}
				}
			}

			$this->imagePath = '/home/erik/Desktop/img';
			$this->outputDir = getcwd() . '/entry_images/char';

			$this->character->setName($this->name);
			$this->character->setRomanji($this->romanji);
			$this->character->setAge($this->age);
			$this->character->setGender($this->gender);
			$this->character->setHeight($this->height);
			$this->character->setWeight($this->weight);
			$this->character->setCup($this->cup);
			$this->character->setBust($this->bust);
			$this->character->setHips($this->hips);
			$this->character->setWaist($this->waist);

			$insert ? $this->insert() : $this->update();
		}

		private function insert()
		{
			app('em')->persist($this->character);

			$this->characterId = app('em')->flush(null,  true);

			$this->makeDirectories();

            $this->insertEntryCharacter();

			$this->outputDir .= $this->characterId;
			if (AdminCheck::checkForLocal()) {
				$images = $this->images;
				$this->images = [];
				$tmpImages = [];
				if ($images['name'][0]) {
					for ($i = 0; $i < count($images['name']); $i++) {
						$image = [
							'name' => $images['name'][$i],
							'tmp' => $images['tmp_name'][$i],
						];
						$tmpImages = array_merge($tmpImages, [$image]);
					}
					$this->images = array_merge($this->images, ['image' => $tmpImages]);
				}

				if ($this->thumbnail['name']) {
					$thumbnail = [[
						'name' => $this->thumbnail['name'],
						'tmp' => $this->thumbnail['tmp_name'],
					]];

				} else {
					$thumbnail = [[
						'name' => '__img.jpg',
						'tmp' => '/home/erik/Desktop/img/__img.jpg',
					]];
				}
				$this->images = array_merge(['img' => $thumbnail], $this->images);
				$this->imageHandler->manipulate($this->characterId, $this->images, self::CHARACTER);
			}

			header('Location: ?v=2&id=' . $this->entry->getId());
		}

		private function update()
		{
			app('em')->update($this->character);
			app('em')->flush();

			if (AdminCheck::checkForLocal()) {
				if ($this->thumbnail['name']) {
					if ($this->thumbnail['name']) {
						$thumbnail = [[
							'name' => $this->thumbnail['name'],
							'tmp' => $this->thumbnail['tmp_name'],
						]];

					} else {
						$thumbnail = [[
							'name' => '__img.jpg',
							'tmp' => '/home/erik/Desktop/img/__img.jpg',
						]];
					}

					$this->images = ['img' => $thumbnail];
					$this->imageHandler->manipulate($this->characterId, $this->images, self::CHARACTER);
				}
			}
		}

        private function insertEntryCharacter()
        {
            $entryCharacter = new EntryCharacter();
            $entryCharacter->setEntry($this->entry->getId());
            $entryCharacter->setCharacter($this->characterId);

            app('em')->persist($entryCharacter);
            app('em')->flush();
        }

		private function handleImages()
		{
			dd($this->images);
		}

		private function isKanji($str)
		{
			return preg_match('/[\x{4E00}-\x{9FBF}]/u', $str) > 0;
		}

		private function isHiragana($str)
		{
			return preg_match('/[\x{3040}-\x{309F}]/u', $str) > 0;
		}

		private function isKatakana($str)
		{
			return preg_match('/[\x{30A0}-\x{30FF}]/u', $str) > 0;
		}

		private function isJapanese($str)
		{
			return $this->isKanji($str) || $this->isHiragana($str) || $this->isKatakana($str);
		}

		private function mb_str_split($string)
		{
			# Split at all position not after the start: ^
			# and not before the end: $
			return preg_split('/(?<!^)(?!$)/u', $string);
		}

		private function makeDirectories()
		{
			$directory = getcwd() . '/entry_images/char/' . $this->characterId;

			if (! file_exists($directory)) {
				mkdir($directory);
				chmod($directory, 0777);
			}
		}
	}