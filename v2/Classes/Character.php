<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 28-1-20
	 * Time: 16:51
	 */

	namespace v2\Classes;

	use v2\Database\Entity\Entry;
    use v2\Database\Repository\EntryRepository;
    use v2\Manager;
    use v2\Traits\TextHandler;

    class Character
	{
        use TextHandler;

		/**
		 * @var string
		 */
		CONST TABLE = Manager::TEST ? 'characters_2' : 'characters';

		/**
		 * @var null|Character
		 */
		private $character = null;

		/**
		 * @var int
		 */
		private $rowNr = 0;

		/**
		 * Character constructor.
		 * @param Character $character
		 */
		public function __construct($character)
		{
			$this->character = $character;

			$file = fopen(Manager::TEMPLATE_FOLDER . 'Character.html', 'r');
			$this->content = fread($file, 100000);

			$this->cssFiles = [
				'Character',
				'EntryInfo',
				'Searcher',
				'OrderBar',
				'EntryList',
			];

			$this->jsFiles = [
				'EntryList',
				'EntryInfo',
				'Searcher',
				'Character',
			];
		}

		/**
		 * Setup all the page info
		 */
		public function buildContent()
		{
			$this->placeHolders = [
				'id'        => $this->character->getId(),
				'romanji'   => $this->character->getRomanji(),
				'name'      => $this->character->getName(),
				'gender'    => $this->character->getGender(),
				'tumbnail'  => getImages($this->character, 'char', 'tumbnail'),
				'list'      => $this->getList(),
				'age'       => $this->setSpace($this->character->getAge()),
				'height'    => $this->setSpace($this->character->getHeight()),
				'weight'    => $this->setSpace($this->character->getWeight()),
				'waist'     => $this->setSpace($this->character->getWaist()),
				'bust'      => $this->setSpace($this->getBust()),
				'hips'      => $this->setSpace($this->character->getHips()),
				'cup'       => $this->getBust() ? null : $this->setSpace($this->character->getCup()),
				'orderBar'  => $this->getOrderBar(),
			];

			$this->ifs = [
				'age'       => $this->character->getAge(),
				'height'    => $this->character->getHeight(),
				'weight'    => $this->character->getWeight(),
				'waist'     => $this->character->getWaist(),
				'bust'      => $this->getBust(),
				'hips'      => $this->character->getHips(),
				'cup'       => $this->getBust() ? null : $this->character->getCup(),
			];

			$images = [];
			foreach (getImages($this->character, 'char') as $image) {
				if (strpos($image, '/..') !== false) {
					continue;
				}
				$images[] = ['image' => $image];
			}

			$this->fors = [
				'images'    => $images,
			];
			$this->getRows();

			$this->fillFors();
			$this->fillIfs();
			$this->fillPlaceHolders();
		}


		public function getList()
		{
			/** @var EntryRepository $entryRepository */
			$entryRepository = app('em')->getRepository(Entry::class);

			$groupBy = ['title', 'ASC'];
			if (request('by') && request('order')) {
				$groupBy = [request('by'), request('order')];
			}

			$limit = [0, 25];
			if ($page = request('p')) {
				$limit = [$page * 25, 25];
			}

			$entries = $entryRepository->findByCharacter($this->character, $groupBy, $limit);

			$list = new GameList($entries);

			$list->buildContent();

			return $list->getContent();
		}

		private function getBust()
		{
			if (($bust = $this->character->getBust()) && ($cup = $this->character->getCup())) {
				return $bust . ' cm (' . $cup . ')';
			}
			if ($bust = $this->character->getBust()) {
				return $bust . ' cm';
			}
			return '';
		}

		public function setSpace($size)
		{
			if (strlen($size) < 3) {
				return '&nbsp;&nbsp;' . $size;
			}
			return $size;
		}

		private function getRows()
		{
			$this->placeHolders = array_merge($this->placeHolders, [
				'rowGender'        => $this->getRowNr($this->placeHolders['gender']),
				'rowAge'           => $this->getRowNr($this->ifs['age']),
				'rowHeight'        => $this->getRowNr($this->ifs['height']),
				'rowWeight'        => $this->getRowNr($this->ifs['weight']),
				'rowBust'          => $this->getRowNr($this->ifs['bust']),
				'rowCup'           => $this->ifs['bust'] ? null : $this->getRowNr($this->ifs['cup']),
				'rowWaist'         => $this->getRowNr($this->ifs['waist']),
				'rowHips'          => $this->getRowNr($this->ifs['hips']),
			]);
		}

		private function getRowNr($value)
		{
			if (! $value) {
				return null;
			}
			$this->rowNr++;

			return (string) ((int) ($this->rowNr % 2 == 1));
		}

		private function getOrderBar()
		{
			$orderBar = new OrderBar('game');

			$orderBar->buildContent();

			return $orderBar->getContent();
		}
	}