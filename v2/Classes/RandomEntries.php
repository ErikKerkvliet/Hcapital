<?php

namespace v2\Classes;

use v2\Database\Entity\Entry;
use v2\Database\Repository\EntryRepository;
use v2\Manager;

class RandomEntries extends TextHandler
{
	/**
	 * DeveloperList constructor.
	 * @param $items
	 */
	public function __construct()
	{
		$file = fopen(Manager::TEMPLATE_FOLDER . 'RandomEntries.html', 'r');
		$this->content = fread($file, 100000);

		$this->cssFiles = [
			'RandomEntries',
		];

		$this->jsFiles = [
			'RandomEntries',
		];
	}

	/**
	 * Setup all the page info
	 */
	public function buildContent()
	{
		$entries = $this->getRandomEntries(['ova', 'game']);

		$this->fors = [
			'entries' => $entries,
			'types' => $this->getTypes(),
		];

		$this->fillFors();

		$ids = array_column($this->fors['entries'], 'id');
		$implodedIds = implode(',', $ids);

		$_SESSION['d'][] = [
			'p' => base64_encode($_SERVER['QUERY_STRING']),
			'i' => base64_encode($implodedIds),
		];


		$this->fillFors();
	}

	public function getRandomEntries($types)
	{
		/** @var EntryRepository $entryRepository */
		$entryRepository = app('em')->getRepository(Entry::class);
		$entities = $entryRepository->findRandomEntries(20, $types);

		$entries = [];
		foreach ($entities as $entity) {
			$entries[] = $this->getEntry($entity);
		}

		return $entries;
	}

	public function loadEntries($ids = [])
	{
		if (! $ids) {
			$data = array_pop($_SESSION['d']);
			$ids = explode(',', base64_decode($data['i']));
		}

		$entryRepository = app('em')->getRepository(Entry::class);
		$entities = $entryRepository->findById($ids);
		$entries = [];
		foreach ($entities as $entity) {
			$entries[] = $this->getEntry($entity);
		}

		return $entries;
	}

	private function getEntry($entry)
	{
		return [
			'id'  => $entry->getId(),
		];
	}

	private function getTypes()
	{
		return [
			['value' => 'ova', 'type' => 'ova', 'checked' => 'checked'],
			['value' => 'game', 'type' => 'game', 'checked' => 'checked'],
			['value' => '3d', 'type' => '3D', 'checked' => ''],
		];
	}
}