<?php

	use v2\Database\Entity\Thread;

	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 20-8-20
	 * Time: 20:40
	 */

	class ThreadFactory
	{
		public function create($data)
		{
			$thread = new Thread();
			foreach ($data as $key => $value) {
				$function = 'set' . ucfirst($key);

				$thread->{$function}($value);
			}
			app('em')->persist($thread);

			return $thread;
		}
	}
