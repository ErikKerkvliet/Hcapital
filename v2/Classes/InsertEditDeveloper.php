<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 5-4-20
	 * Time: 16:38
	 */

	namespace v2\Classes;

	use v2\Database\Entity\Developer;
    use v2\Manager;
    use v2\Traits\TextHandler;


    class InsertEditDeveloper
	{
        use TextHandler;

		/**
		 * @var Developer|null
		 */
		private $developer = 0;

		/**
		 * Home constructor.
		 * @param null $developer
		 */
		public function __construct($developer = null)
		{
			$this->developer = $developer;

			$file = fopen(Manager::TEMPLATE_FOLDER . 'InsertEditDeveloper.html', 'r');
			$this->content = fread($file, 10000);

			$this->cssFiles = [
				'InsertEdit'
			];

			$this->jsFiles = [
				'Home',
			];
		}

		public function buildContent()
		{
			$developerId = $this->developer ? $this->developer->getId() : 0;
			$action = $developerId ? 'editDeveloper&did=' . $this->developer->getId() :	'insertDeveloper';

			$this->placeHolders = [
				'id'          => $developerId,
				'action'      => $action,
				'name'        => $developerId ? $this->developer->getName() : '',
				'kanji'       => $developerId ? $this->developer->getKanji() : '',
				'homepage'    => $developerId ? $this->developer->getHomepage() : '',
				'type'        => $developerId ? $this->developer->getType() : '',
			];

			$this->fillPlaceHolders();
		}
	}