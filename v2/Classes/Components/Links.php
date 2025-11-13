<?php

    namespace v2\Classes;

    use HostResolver;
    use v2\Database\Entity\Entry;
    use v2\Database\Entity\Link;
    use v2\Database\Repository\LinkRepository;
    use v2\Manager;
    use v2\Traits\TextHandler;

    class Links
    {
        use TextHandler;

        const NO_COMMENT = 'no_comment';

        /**
         * @var Link[]
         */
        private $links;

        /**
         * @var array
         */
        private $groupedLinks = [];

        /** @var HostResolver */
        private $hostResolver;

        public function __construct(Entry $entry)
        {
            $file = fopen(Manager::COMPONENT_FOLDER . 'LinksBox.html', 'r');
            $this->content = fread($file, 100000);

            $this->hostResolver = new HostResolver();

            /** @var LinkRepository $linkRepository */
            $linkRepository = app('em')->getRepository(Link::class);

            $this->links = $linkRepository->findBy(['entry' => $entry], ['part' => 'ASC']);
            $this->groupLinks();
        }

        public function buildContent()
        {
            $this->fors = [
                'links' => $this->getLinks(),
            ];

            $this->placeHolders = [
                'otherLinks' => $this->getOtherLinks(),
            ];

            $this->fillFors();
        }

        private function getLinks()
        {
            $links = array_key_exists(self::NO_COMMENT, $this->groupedLinks) ?
                $this->groupedLinks[self::NO_COMMENT] : $this->groupedLinks[array_keys($this->groupedLinks)[0]];

            if (count($links) == 1) {
                $this->links = [
                    'id'    => $link->getId(),
                    'text'  => $this->getText(),
                ];
            }

            foreach ($links as $link) {
                $links[] = [
                    'id'    => $link->getId(),
                    'text'  => $this->getText(),
                ];
            }
        }

        private function groupLinks()
        {
            foreach ($this->links as $link) {
                $key = ($comment = $link->getComment()) ? $comment : self::NO_COMMENT;
                $host = $this->hostResolver->byUrl($link->getUrl());
                $this->groupedLinks[$key][$host][] = $link;
            }
        }
    }