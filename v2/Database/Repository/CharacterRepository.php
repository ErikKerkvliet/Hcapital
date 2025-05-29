<?php
	/**
	 * Created by PhpStorm.
	 * User: erik
	 * Date: 27-12-19
	 * Time: 21:50
	 */

	namespace v2\Database\Repository;

	use v2\Database\Entity\Character;
	use v2\Database\Entity\EntryCharacter;

	class CharacterRepository extends Repository
	{
		/**
		 * Entity class of repository
		 */
		protected $entity = Character::class;

		/**
		 * @param string $search
		 * @param array $orderBy
		 * @param array $limit
		 * @return array
		 */
		public function findBySearch(
			$search,
			array $orderBy = ['name', 'desc'],
			array $limit = [0, 25]
		) {
			if ($search == null) {
//				$orderBy[0] = $orderBy[0] != 'gender' ? $orderBy[0] : 'gender';

//				$orderBy[0] = $orderBy[0] != 'romanji'  ? $orderBy[0] : 'name_romanji';
				$characters = $this->select()
					->from(Character::TABLE, 'c')
					->orderBy($orderBy[0], $orderBy[1])
					->limit($limit[0], $limit[1])
					->getResult();
				
				$noKanji = [];
				$kanji = [];
				foreach ($characters as $character) {
					if (($name = $character->getName()) == '' || $name == '？？') {
						$noKanji[] = $character;
					} else {
						$kanji[] = $character;
					}
				}

				return array_merge($kanji, $noKanji);
			}

//			$search = $this->validateForQuery($search);

//			$orderBy[0] = $orderBy[0] != 'gender' ?: 'gender';
//			$orderBy[0] = $orderBy[0] != 'romanji' ?: 'name_romanji';

			$search = trim($search, ' ');

			if (strlen($search) < 3) {
				return [];
			}
			$this->select()
				->from(Character::TABLE, 'c');

			$words = explode(' ', $search);

			$wordCount = count($words);
//			$this->where('"' . $search . '"', 'REGEXP', 'name', '(');
//			$this->orWhere('"' . $search . '"', 'REGEXP', 'name_romanji', '', ')');

			foreach ($words as $key => $word) {
				if ($key === 0) {
					$eb = $wordCount == 1 ? ')' : '';
					$this->where('name', 'LIKE', "'%" . $word . "%'", '((', $eb);
					continue;
				}
				if ($key === $wordCount - 1) {
					$this->andWhere('name', 'LIKE', "'%" . $word . "%'", '', ')');
					continue;
				}
				$this->andWhere('name', 'LIKE', "'%" . $word . "%'");
			}

			$this->or();

			foreach ($words as $key => $word) {
				if ($key === 0) {
					$eb = $wordCount == 1 ? '))' : '';

					$this->whereSingle('romanji', 'LIKE', "'%" . $word . "%'", '(', $eb);
					continue;
				}
				if ($key === $wordCount - 1) {
					$this->andWhere('romanji', 'LIKE', "'%" . $word . "%'", '', '))');
					continue;
				}
				$this->andWhere('romanji', 'LIKE', "'%" . $word . "%'");
			}

			if ($orderBy) {
				$this->orderBy($orderBy[0], $orderBy[1]);
			}
			if ($limit) {
				$this->limit($limit[0], $limit[1]);
			}

			return $this->getResult();
		}

		/**
		 * @param $char
		 * @param array $orderBy
		 * @param array $limit
		 * @return array|int|mixed
		 */
		public function findByChar(
			$char,
			array $orderBy = [],
			array $limit = []
		) {
			$condition = '=';
			if ($char == '35') {
				$condition = 'REGEXP';
				$char = "[^A-Za-z]";
			}
			$this->select()
				->from(Character::TABLE, 'c')
				->where('LEFT(romanji, 1)', $condition, "'" . $char . "'")
				->orderBy($orderBy[0], $orderBy[1])
				->limit($limit[0], $limit[1]);

			return $this->getResult();
		}

		public function findByEntry($entry)
		{
			$id = is_int($entry) ? $entry : $entry->getId();

			return $this->select()
				->from(Character::TABLE, 'c')
				->leftJoin(EntryCharacter::TABLE, 'ec', 'ec.character_id', '=', 'c.id')
				->where('ed.entry_id', '=', $id)
				->getResult();
		}
	}