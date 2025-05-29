<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 30-11-19
	 * Time: 20:36
	 */

	namespace v2\Database\Entity;

	use v2\Classes\AdminCheck;
	use v2\Database\EntityManager;

	use v2\Database\Entity\Banned;
	use v2\Database\Entity\Broken;
	use v2\Database\Entity\Character;
	use v2\Database\Entity\EntryCharacter;
	use v2\Database\Entity\Developer;
	use v2\Database\Entity\DeveloperRelation;
	use v2\Database\Entity\Download;
	use v2\Database\Entity\Entry;
	use v2\Database\Entity\EntryRelation;
	use v2\Database\Entity\Link;

	class Entity
	{
		/**
		 * @var bool
		 */
		protected $initialized = false;

		/**
		 * @var /EntityManager|null
		 */
		protected $em = null;

		/**
		 * @var array
		 */
		protected $originalValues = [];

		/**
		 * Entity constructor.
		 * @param null $id
		 */
		public function __construct($id = 0)
		{
			$this->em = app('em');
			if (is_numeric($id)) {
				$this->id = (int) $id;
			} else if ($id && ! is_numeric($id)) {
				if (AdminCheck::checkForAdmin()) {
					dd('Incorrect ' . get_class($this) . ' id: (' . $id . ')');
				}
			}
		}

		/**
		 * @param $initialized
		 */
		public function setInitialized($initialized)
		{
			$this->initialized = $initialized;
		}

		/**
		 * @return bool
		 */
		public function isInitialized(): bool
		{
			return $this->initialized;
		}

		/**
		 * @return array
		 */
		public function getOriginalValues()
		{
			return $this->originalValues;
		}

		/**
		 * @param array $originalValues
		 */
		public function setOriginalValues(array $originalValues)
		{
			$this->originalValues = $originalValues;
		}

		/**
		 * @param bool $onlyId
		 * @return mixed
		 */
		protected function getEntity($onlyId = false)
		{
			$backTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2);
			$caller = isset($backTrace[1]['function']) ? $backTrace[1]['function'] : null;
			$var = lcfirst(substr($caller, 3));

			if ($onlyId) {
//				dc($this->{$var});
				return $this->{$var};
			}
			if (strpos($var, 'developer') !== false ||
				($this::TABLE == 'developer_relations' && $var == 'relation')) {
				$EntityClass = 'v2\Database\Entity\Developer';
			} else if ($var == 'relation') {
				$EntityClass = 'v2\Database\Entity\Entry';
			} else {
				$EntityClass = 'v2\Database\Entity\\' . ucfirst($var);
			}
			if (is_null($this->{$var})) {
				return null;
			};
			return (! $this->initialized || (is_object($this->{$var}) && get_class($this->{$var}) == $EntityClass)) ?
				$this->{$var} :	$this->{$var} = $this->em->find($EntityClass, $this->{$var}) ?: null;
		}

		/**
		 * @param $entity
		 * @return int
		 * @throws \Exception
		 */
		protected function setEntity($entity): int
		{
			if ($int = (is_string($entity) || is_int($entity)) || get_class($entity) === get_class($this)) {
				return $int ? $entity : $entity->getId();
			}
			return 0;
		}
	}
