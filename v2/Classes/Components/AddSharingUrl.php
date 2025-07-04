<?php

use v2\Traits\TextHandler;

/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 13-12-21
	 * Time: 21:21
	 */

	class AddSharingUrl
	{
        use TextHandler;

		/**
		 * @var int
		 */
		private $nr;

		/**
		 * @var int
		 */
		private $entryId;

		/**
		 * @var int
		 */
		private $threadId;

		/**
		 * @var int
		 */
		private $entryType;

		/**
		 * @var int
		 */
		private $author;

		/**
		 * AddSharingUrl constructor.
		 * @param $nr
		 * @param $entryId
		 * @param $threadId
		 * @param $entryType
		 * @param $author
		 */
		public function __construct($nr, $entryId, $threadId, $author)
		{
			$this->nr = $nr;
			$this->entryId = $entryId;
			$this->threadId = $threadId;
			$this->author = $author;

			$file = fopen(\v2\Manager::COMPONENT_FOLDER . 'AddSharingUrl.html', 'r');
			$this->content = fread($file, 100000);
		}

		public function buildContent()
		{
			$this->placeHolders = [
				'nr'    => ($this->nr),
				'entry-id' => $this->entryId,
				'author' => $this->author,
				'id' => 0,
				'update' => 'Create',
				'options' => $this->getSharingOptions(),
			];

			$this->fillPlaceHolders();
		}

		private function getSharingOptions()
		{
			$optionsStr = '';
			$usernames = getenv('SHARING_USERNAMES');
			if ($usernames) {
				$usernames = explode(',', $usernames);
				foreach ($usernames as $username) {
					$optionsStr .= '<option value="' . $username . '">' . $username . '</option>';
				}
			}
			return $optionsStr;
		}
	}