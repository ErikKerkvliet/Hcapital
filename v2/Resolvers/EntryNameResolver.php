<?php

	use v2\Database\Entity\Entry;
	use v2\Database\Entity\EntryRelation;

	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 31-1-21
	 * Time: 17:44
	 */
	class EntryNameResolver
	{
		private $type;

		private $title;

		private $entry = null;

		private $relationHtml;

		public function resolveByTypeAndTitle(string $type, string $title): string
		{
			$this->type = $type;
			$this->title = $title;

			return $this->type == 'game' ? $this->handleGame() : $this->handleOva();
		}

		public function resolveByEntry(Entry $entry, $relationsHtml = ''): string
		{
			$this->entry = $entry;
			$this->relationHtml = $relationsHtml;

			return $this->resolveByTypeAndTitle($entry->getType(), $entry->getTitle());
		}

		private function handleOva(): string
		{
			if (!$this->entry) {
				return $this->title;
			}

			$entryRelationRepository = app('em')->getRepository(EntryRelation::class);
			$entryRelation = $entryRelationRepository->findOneBy(['entry' => $this->entry]);

			$relations = $entryRelation ? $entryRelationRepository->findBy(['relation' => $entryRelation->getRelation()]) : [];

			$title = $this->title;
			if (!$relations) {
				return trim($this->handleDevelopers($title));
			}

			return trim($this->handleDevelopers($title));
		}

		private function handleDevelopers(string $title): string
		{
			$entryDeveloperRepository = app('em')->getRepository(\v2\Database\Entity\EntryDeveloper::class);

			if (!$this->entry) {
				return $title;
			}
			$entryDevelopers = $entryDeveloperRepository->findBy(['entry' => $this->entry]);

			foreach ($entryDevelopers as $entryDeveloper) {
				// developers: 'WORLD PG ANIMATION / Survive / Survive more / Appetite'
				$ids = [799, 802, 803, 1071];
				if (in_array($entryDeveloper->getDeveloper(true), $ids)) {
					if (($pos = strpos($title, 'The Motion Anime'))) {
						$substring = substr($title, $pos);
						$title = str_replace($substring, '<br>' . $substring, $title);
					} else {
						$title .= '<br>The Motion Anime';
					}
					if (($pos = strpos($title, ' Vol. 0')) && empty($this->relationHtml)) {
						$title = substr($title, 0, $pos);
					}
				}
			}
			return $title;
		}

		private function handleGame(): string
		{
			return $this->title;
		}
	}