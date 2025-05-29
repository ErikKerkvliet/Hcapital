<?php

	namespace v2\Factories;
	
	use v2\Database\Entity\Thread;

	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 20-8-20
	 * Time: 20:40
	 */

	class ThreadFactory
	{
		/**
		 * 
		 * @param mixed $data
		 * @return Thread
		 */
		public function create($data): Thread
		{
			$thread = new Thread();
			$thread = $this->fill($thread, $data);

			app('em')->persist($thread);

			return $thread;
		}

		/**
		 * @param Thread $thread
		 * @param array $data
		 * @return Thread
		 */
		public function update(Thread $thread, array $data): Thread
		{
			return $this->fill($thread, $data);
		}

		/**
		 * @param Thread $thread
		 * @param array $data
		 * @return Thread
		 */
		private function fill(Thread $thread, array $data): Thread
		{
			foreach ($data as $key => $value) {
				$function = 'set' . ucfirst($key);

				$thread->{$function}($value);
			}
			return $thread;
		}
	}
