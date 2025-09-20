<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 17-05-2024
 * Time: 10:00
 */

namespace v2\Classes;

use v2\Database\Entity\Link;
use v2\Database\Repository\LinkRepository;

class LinkManager
{
    /** @var LinkRepository */
    private $linkRepository;

    public function __construct()
    {
        // Get the repository using your application's service locator/helper
        $this->linkRepository = app('em')->getRepository(Link::class);
    }

    /**
     * Synchronizes the links for a given entry.
     * It adds new links, deletes old ones that are not in the new dataset,
     * and leaves untouched ones as they are.
     *
     * @param int $entryId The ID of the entry to update.
     * @param array $submittedLinks An array of link data from the form.
     * Expected format: [['url' => '...', 'comment' => '...'], ...]
     */
    public function synchronizeLinks(int $entryId, array $submittedLinks)
    {
        // 1. Fetch all existing links for the entry and map them by their URL for quick lookup.
        $existingLinks = $this->linkRepository->findBy(['entry' => $entryId]);
        $existingLinksByUrl = [];
        foreach ($existingLinks as $link) {
            $existingLinksByUrl[$link->getLink()] = $link;
        }

        // 2. Iterate through submitted links to find what's new or what has stayed.
        foreach ($submittedLinks as $submittedLinkData) {
            $url = $submittedLinkData['url'];
            $comment = $submittedLinkData['comment'];

            // Check if this submitted link already exists in the database.
            if (isset($existingLinksByUrl[$url])) {
                // The link exists. We can potentially update its comment if it has changed.
                $existingLink = $existingLinksByUrl[$url];
                if ($existingLink->getLink() !== $url && $existingLink->getPart() !== $this->getPart($url)) {
                    $existingLink->setComment($comment);
                    $existingLink->setCreated(date('Y-m-d H:i:s'));
                    app('em')->update($existingLink);
                }

                // Since we've "seen" this link, remove it from our map.
                // What's left in the map at the end will be the links to be deleted.
                unset($existingLinksByUrl[$url]);

            } else {
                // The link is new. Create and persist a new Link entity.
                $newLink = new Link();
                $newLink->setEntry($entryId);
                $newLink->setLink($url);
                $newLink->setComment($comment);
                $newLink->setPart($this->getPart($url));
                $newLink->setCreated(date('Y-m-d H:i:s'));

                app('em')->persist($newLink);
            }
        }

        // 3. Delete old links.
        // Any links remaining in $existingLinksByUrl were not in the submission, so they should be deleted.
        if (!empty($existingLinksByUrl)) {
            foreach ($existingLinksByUrl as $linkToDelete) {
                app('em')->delete($linkToDelete);
            }
        }

        // 4. Flush all changes (inserts, updates, deletes) to the database in one go.
        app('em')->flush();
    }

    /**
     * Extracts the part number from a link URL.
     *
     * @param string $link
     * @return int
     */
    private function getPart(string $link): int
    {
        $exploded = explode('/', $link);
        $fileName = end($exploded);

        if (strpos($fileName, '.part') !== false) {
            $fileNameParts = explode('.', $fileName);
            // Assuming format is always filename.partXX.rar
            foreach ($fileNameParts as $part) {
                if (substr($part, 0, 4) === 'part') {
                    return (int) ltrim($part, 'part');
                }
            }
        }
        return 0;
    }
}