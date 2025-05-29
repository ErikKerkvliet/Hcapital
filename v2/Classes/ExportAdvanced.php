<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 24-10-23
	 * Time: 11:28
	 */

	namespace v2\Classes;

	use v2\Manager;
    use v2\Traits\TextHandler;

    class ExportAdvanced
	{
        use TextHandler;

		private $entryIds = [];

		private $type;

		private $from = 0;

		private $to = 0;

		private $multiple = false;

		/**
		 * ExportAdvanced constructor.
		 * @param $entryIds
		 * @param string $type
		 * @param int $from
		 * @param int $to
		 * @param bool $multiple
		 */
		public function __construct($entryIds, $type = 'entry', $multiple = false, $from = 0, $to = 0)
		{
			$this->entryIds = $entryIds;
			$this->type = $type;
			$this->from = $from;
			$this->to = $to;
			$this->multiple = $multiple;

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

		public function getExportData()
		{
			$export = new Export($this->entryIds, $this->type, $this->multiple);

			return [
				'message' => $export->getExportEntry(),
				'errors' => $export->getErrors(),
			];
		}
	}