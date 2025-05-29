<?php

	namespace v2\Factories;

	use v2\Database\Entity\Link;

	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 28-8-20
	 * Time: 21:14
	 */

	class LinkFactory
	{
		public function create($data)
		{
			$thread = new Link();
			foreach ($data as $key => $value) {
				$function = 'set' . ucfirst($key);

				$thread->{$function}($value);
			}

			app('em')->persist($thread);

			app('em')->flush($thread);

			return $thread;
		}
	}