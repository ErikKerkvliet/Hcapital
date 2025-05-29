<?php

namespace v2\Transformers;

use v2\Database\Entity\Entry;

class EntryTransformer
{
    private $return = 'full';

    public function __construct($return = 'full')
    {
        $this->return = $return;
    }

    /**
     * @param Entry[] $entries
     * @return array
     */
    public function transform(array $entries): array
    {
        if ($this->return == 'simple') {
            return array_map(function ($entry) { return ['id' => $entry->getId()]; }, $entries);
        }

        return array_map(function ($entry) {
            return [
                'id' => $entry->getId(),
                'kanji' => $entry->getTitle(),
                'romanji' => $entry->getRomanji(),
                'released' => $entry->getReleased(),
                'size' => $entry->getSize(),
                'website' => $entry->getWebsite(),
                'information' => $entry->getInformation(),
                'password' => $entry->getPassword(),
                'type' => $entry->getType(),
                'timeType' => $entry->getTimeType(),
                'lastEdit' => $entry->getLastEdit(),
                'created' => $entry->getCreated(),
                'downloads' => $entry->getDownloads(),
            ];
            }, $entries);
    }
}