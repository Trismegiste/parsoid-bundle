<?php

/*
 * Parsoid bundle
 */

namespace Trismegiste\ParsoidBundle;

use Override;
use Wikimedia\Parsoid\DOM\Element;

/**
 * No override of links in the parsoid rendering
 */
class LinkDefault extends LinkOverride
{

    #[Override]
    protected function transformFileDom(Element $container, Element $link, Element $img, string $wikiFilename): void
    {
        
    }

    #[Override]
    protected function transformLinkDom(Element $link, string $wikilink): void
    {
        
    }

    #[Override]
    protected function transformMissingFileDom(Element $container, Element $link, Element $info, string $wikiFilename): void
    {
        
    }
}
