<?php

namespace v2\Transformers;


use v2\Database\Entity\Character;
use v2\Database\Entity\EntryCharacter;
use v2\Database\Repository\EntryCharacterRepository;

class CharacterTransformer
{
    public function transform(array $characters)
    {
        return array_map(function ($character) {
            return [
                'id' => $character->getId(),
                'kanji' => $character->getName(),
                'romanji' => $character->getRomanji(),
                'age' => $character->getAge(),
                'gender' => $character->getGender(),
                'height' => $character->getHeight(),
                'weight' => $character->getWeight(),
                'cup' => $character->getCup(),
                'bust' => $character->getBust(),
                'waist' => $character->getWaist(),
                'hips' => $character->getHips(),
                'entries' => $this->getEntries($character),
            ];
        }, $characters);
    }

    private function getEntries(Character $character)
    {
        /** @var EntryCharacterRepository $entryCharactersRepository */
        $entryCharactersRepository = app('em')->getRepository(EntryCharacter::class);
        $entryTransformer = new EntryTransformer('simple');

        $entries = $entryCharactersRepository->findEntryByCharacters($character);

        return implode(', ', array_map(function($entry) {
           return $entry['id'];
        }, $entryTransformer->transform($entries)));
    }
}