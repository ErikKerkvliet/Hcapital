<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 22-10-23
	 * Time: 18:17
	 */

	namespace v2\Classes;

	loadEnv(str_replace('Classes', '', __DIR__) . '.env');

    use v2\Database\Entity\Banned as BannedEntity;
    use v2\Manager;
    use v2\Traits\TextHandler;

    class Banned
	{
        use TextHandler;

		private $banned = [];

		/**
		 * LinkState constructor.
		 * @param null|int $entry
		 */
		public function __construct()
		{
			$file = fopen(Manager::TEMPLATE_FOLDER . 'Banned.html', 'r');
			$this->content = fread($file, 10000);
			$this->cssFiles = [
				'Home',
				'Banned',
			];

			$this->jsFiles = [
				'Banned'
			];

			$this->setBanned();
        }

		public function buildContent()
		{
			$this->fors = [
				'banned'  => $this->banned,
			];

			$this->fillFors();
		}

		private function setBanned()
		{
			$bannedRepository = app('em')->getRepository(BannedEntity::class);
			$items = $bannedRepository->findAll();

			$row = 0;
			foreach($items as $key => $item) {
				$this->banned[] = [
					'tr' => 'row-color-' . ($row % 2),
					'id' => $item->getId(),
                    'ip' => $item->getIp(),
					'entry' => $item->getEntry(true) ?: '',
					'location' => $item->getLocation(),
					'postal' => $item->getPostal() ?: '',
				];
				$row++;
			}
		}
	}