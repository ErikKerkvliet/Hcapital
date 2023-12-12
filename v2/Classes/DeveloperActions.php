<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 12-4-20
	 * Time: 17:59
	 */

	namespace v2\Classes;

	use v2\Database\Entity\Developer;

	class DeveloperActions
	{
		private $developer = null;

		private $insert = false;

		private $name = '';
		private $kanji = '';
		private $homepage = '';
		private $type = '';

		public function __construct($insert = false, $id = 0)
		{
			$this->insert = $insert;

			if ($this->insert) {
				$this->developer = new Developer();
			} else {
				$this->developer = app('em')->find(Developer::class, $id);
			}

			$this->name = request('name') ?: '';
			$this->kanji = request('kanji') ?: '';
			$this->homepage = request('homepage') ?: '';
			$this->type = request('type') ?: '';


			$this->developer->setName($this->name);
			$this->developer->setKanji($this->kanji);
			$this->developer->setHomepage($this->homepage);
			$this->developer->setType($this->type);

			$insert ? $this->insert() : $this->update();
		}

		private function insert()
		{
			app('em')->persist($this->developer);

			$this->developerId = app('em')->flush(null,  true);

			header('Location: /');
		}

		private function update()
		{
			app('em')->update($this->developer);
			app('em')->flush();
		}
	}