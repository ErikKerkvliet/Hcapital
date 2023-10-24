<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 24-10-23
	 * Time: 11:28
	 */

	namespace v2\Classes;

	use v2\Manager;

	class ExportAdvanced extends TextHandler
	{
		private $entryIds = [];

		private $type;

		private $from = 0;

		private $to = 0;

		private $all = false;


		/**
		 * ExportAdvanced constructor.
		 * @param $entryIds
		 * @param string $type
		 * @param int $from
		 * @param int $to
		 * @param bool $all
		 */
		public function __construct($entryIds, $type = 'entry', $all = false, $from = 0, $to = 0)
		{
			$this->entryIds = $entryIds;
			$this->type = $type;
			$this->from = $from;
			$this->to = $to;
			$this->all = $all;

			$file = fopen(Manager::TEMPLATE_FOLDER . 'ExportAdvanced.html', 'r');
			$this->content = fread($file, 10000);
			$this->cssFiles = [
				'ExportAdvanced',
			];

			$this->jsFiles = [
				'ExportAdvanced',
			];
		}

		public function buildContent()
		{
			$this->placeHolders = [
				'type' => $this->type,
				'ids' => implode(',', $this->entryIds),
			];

			$this->fillPlaceHolders();
		}

		public function getExportCode()
		{
			$export = new Export($this->entryIds, $this->type, $this->all);
			return $export->getExportEntry();
		}

	}