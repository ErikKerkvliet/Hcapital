<?php
namespace v2\Builders;

use HostResolver;
use v2\Manager;
use v2\Traits\Builder;
use v2\Traits\TextHandler;

class Links3
{
    use Builder;
    use TextHandler;

    /**
     * @var array
     */
    private $links = [];

    /**
     * @var HostResolver
     */
    private $hostResolver;

    /**
     * @var bool
     */
    private $single = false;

    public function buildContent()
    {
        $file = fopen(Manager::COMPONENT_FOLDER . '/LinksBox.html', 'r');
        $this->content = fread($file, 10000);
    }
}
