<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 12-2-20
	 * Time: 23:27
	 */

	namespace v2\Classes;


	use v2\Database\Entity\Entry;
	use v2\Database\Repository\EntryRepository;
	use v2\Manager;

	class Navigator extends TextHandler
	{
		CONST navigatorButtonAmount = 6;

		private $timeType = '';

		private $navigatorButtons = [];

		public function __construct($timeType)
		{
			$this->timeType = $timeType;

			$this->getNavigationButtons();
		}

		public function buildContent()
		{
			$file = fopen(Manager::TEMPLATE_FOLDER . 'Navigator.html', 'r');
			$this->content = fread($file, 10000);

			$this->placeHolders = [
				'type'  => $this->timeType,
			];

			$this->fors = [
				'buttons-left'  => $this->navigatorButtons['game'],
				'buttons-right' => ! AdminCheck::checkForLocal() ? $this->navigatorButtons['ova'] :
					$this->navigatorButtons['ova'] + $this->navigatorButtons['app'],
			];

			$this->fillPlaceHolders();
			$this->fillFors();
		}

		private function getNavigationButtons()
		{
			$buttons['game'] = $this::navigatorButtonAmount;
			$buttons['ova'] = $this::navigatorButtonAmount;
			$buttons['app'] = $this::navigatorButtonAmount;
			$buttons['3d'] = 0;
			if ($this->timeType == 'upcoming') {
				/** @var EntryRepository $entryRepository */
				$entryRepository = app('em')->getRepository(Entry::class);
				$buttons = $entryRepository->getUpcomingEntryCount();

				foreach($buttons as $key => $button) {
					$buttonAmount = ceil($buttons[$key] / 10);
					$buttons[$key] = $buttonAmount >= $this::navigatorButtonAmount ? $this::navigatorButtonAmount :
						$buttonAmount;
				}
			}

			foreach($buttons as $key => $button) {
				$this->navigatorButtons[$key] = [];
				for ($i = 0; $i < $button; $i++) {
					$this->navigatorButtons[$key][] = ['nr' => $i + 1];
				}
			}
			return true;
		}
	}