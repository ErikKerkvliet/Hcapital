<?php

use v2\Database\Entity\Banned;
use v2\Database\Entity\Download;
use v2\Database\Entity\EntryCharacter;
use v2\Database\Entity\EntryDeveloper;
use v2\Database\Entity\EntryRelation;
use v2\Database\Entity\Link;
use v2\Database\Entity\Thread;
use v2\Database\EntityManager;

/**
 * Handler for deleting entities and their relations.
 * Handles deletion of entries, developers, characters, links, threads, and relations.
 * Can delete single entities or bulk delete based on entry relations.
 */
class DeleteHandler
{
    /** @var EntityManager */
    private $em;
    
    public function __construct()
    {
        $this->em = app('em');
    }

    /**
     * Delete an entry and all related entities.
     */
    public function deleteEntry($entry)
    {
        $this->deleteEntryDevelopers($entry);
        $this->deleteEntryCharacters($entry);
        $this->deleteLinks($entry);
        $this->deleteThreads($entry);
        $this->deleteByEntryAndRelation($entry);
        
        $this->deleteEntity($entry);
    }

    /**
     * Delete all developers related to an entry.
     */
    public function deleteEntryDevelopers($entry)
    {
        $entryDevelopers = $this->em->getRepository(EntryDeveloper::class)
            ->findBy(['entry' => $entry]);
        
        foreach ($entryDevelopers as $entryDeveloper) {
            $this->deleteEntryDeveloper($entryDeveloper->getDeveloper(), $entry);
        }
    }

    /**
     * Delete a single developer and its entry relations.
     */
    public function deleteEntryDeveloper($developer, $entry)
    {
        $entryDevelopers = $this->em->getRepository(EntryDeveloper::class)
            ->findBy(['developer' => $developer]);

        foreach ($entryDevelopers as $entryDeveloper) {
            if ($entry === null || $entryDeveloper->getEntry(true) == $entry->getId()) {
                $this->deleteEntity($entryDeveloper);
            }
        }
        
        if (count($entryDevelopers) === 1) {
            $this->deleteEntity($developer);
        }
    }

    /**
     * Delete all entry characters related to an entry.
     */
    public function deleteEntryCharacters($entry)
    {
        $entryCharacters = $this->em->getRepository(EntryCharacter::class)
            ->findBy(['entry' => $entry]);

        foreach ($entryCharacters as $entryCharacter) {
            $this->deleteEntryCharacter($entryCharacter->getCharacter(), $entry);
        }
    }

    /**
     * Delete a single character and its entry relations.
     */
    public function deleteEntryCharacter($character, $entry)
    {
        $entryCharacters = $this->em->getRepository(EntryCharacter::class)
            ->findBy(['character' => $character]);

        foreach ($entryCharacters as $entryCharacter) {
            if ($entry === null || $entryCharacter->getEntry(true) == $entry->getId()) {
                $this->deleteEntity($entryCharacter);
            }
        }
        
        if (count($entryCharacters) === 1) {
            $this->deleteEntity($character);
        }
    }

    /**
     * Delete a single developer and its entry relations
     */
    public function deleteDeveloper($developer)
    {
        // Find all entries related to the developer and delete them
        $entryDevelopers = $this->em->getRepository(EntryDeveloper::class)->findBy(['developer' => $developer]);

        foreach ($entryDevelopers as $entryDeveloper) {
            $this->deleteEntity($entryDeveloper);
        }

        $this->deleteEntity($developer);
    }

   /**
     * Delete a single character and its entry relations
     */
    public function deleteCharacter($character)
    {
        $entryCharacters = $this->em->getRepository(EntryCharacter::class)->findBy(['character' => $character]);

        foreach ($entryCharacters as $entryCharacter) {
            $this->deleteEntity($entryCharacter);
        }

        $this->deleteEntity($character);
    }

    /**
     * Delete all links related to an entry.
     */
    public function deleteLinks($entry)
    {
        $links = $this->em->getRepository(Link::class)
            ->findBy(['entry' => $entry]);

        foreach ($links as $link) {
            $this->deleteEntity($link);
        }
    }

        /**
     * Delete all links by entry and comment.
     */
    public function deleteLinksByEntryAndComment($entry, $comment = '')
    {
        $findBy = ['entry' => $entry];
        if ($comment) {
            $findBy['comment'] = $comment;
        }
        $links = $this->em->getRepository(Link::class)
            ->findBy($findBy);


        foreach ($links as $link) {
            $this->deleteEntity($link);
        }
    }

    public function deleteByEntyAndHost($entry, $host, $parts = []) {
        $this->em->getRepository(Link::class)
            ->deleteByHost((int) $entry, $host, $parts);
    }

    /**
     * Delete all threads related to an entry.
     */
    public function deleteThreads($entry, $number = null)
    {
        $findBy = ['entry' => $entry];

        if ($number) {
            $findBy['number'] = $number;
        }

        $entryThreads = $this->em->getRepository(Thread::class)
            ->findBy($findBy);

        foreach ($entryThreads as $entryThread) {
            $this->deleteEntity($entryThread);
        }
    }

    /**
     * Delete a single entry character by entry and character.
     */
    public function deleteByEntryAndCharacter($entry, $character)
    {
        $entryCharacters = $this->em->getRepository(EntryCharacter::class)
            ->findBy(['character' => $character]);
        $entryCharacter = $this->em->getRepository(EntryCharacter::class)
            ->findBy(['entry' => $entry, 'character' => $character]);

        $this->deleteEntity($entryCharacter);

        if (count($entryCharacters) === 1) {
            $this->deleteEntity($entryCharacters[0]);
        }        
    }

    /**
     * Delete all entry relations related to an entry and relation.
     */
    public function deleteByEntryAndRelation($entry, $relation = null)
    {
        if ($relation) {
            $entryRelations = $this->em->getRepository(EntryRelation::class)
                ->findRelationsByEntryAndRelation($entry, $relation);
        } else {
            $entryRelations = $this->em->getRepository(EntryRelation::class)
                ->findBy(['entry' => $entry]);
        }

        foreach ($entryRelations as $entryRelation) {
            $this->deleteEntity($entryRelation);
        }
    }

    /**
     * Delete a single entry developer.
     */
    public function deleteByEntryAndDeveloper($entry, $developer)
    {
        $entryDevelopers = $this->em->getRepository(EntryDeveloper::class)
            ->findBy(['entry' => $entry, 'developer' => $developer]);

        $this->deleteEntity($entryDevelopers);
    }

    public function deleteBanned($ip)
    {
        $bannedRepository = app('em')->getRepository(Banned::class);
        $entities = $bannedRepository->findBy(['ip' => $ip]);

        foreach($entities as $entity) {
            app('em')->delete($entity);
        }
    }

    public function deleteDownloads($entry, $date)
    {
        $downloadRepository = $this->em->getRepository(Download::class);
        $downloadRepository->deleteDownloads($entry, $date);
    }

    /**
     * Delete an entity from the database.
     */
    public function deleteEntity($entity)
    {
        // dc($entity);
        $this->em->delete($entity);
    }
}
