<?php

	namespace v2\Factories;

	/**
	 * Created by VSCode.
	 * User: erik
	 * Date: 26-7-25
	 * Time: 23:21
	 */

	class FactoryAbstract
	{
		public function create($data, $flush = false)
		{
			$entity = new $this->entity();
			$this->fill($entity, $data);

			app('em')->persist($entity);

			if ($flush) {
				app('em')->flush($entity);
			}

			return $entity;
		}

		/**
		 * @param Object $entity
		 * @param array $data
		 * @return Object
		 */
		public function update($entity, array $data)
		{
			return $this->fill($entity, $data);
		}


		public function fill($entity, $data)
		{
			foreach ($data as $key => $value) {
				$function = 'set' . ucfirst($key);
				$entity->{$function}($value);
			}
			return $entity;
		}
	}