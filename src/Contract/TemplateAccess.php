<?php

/*
 * Parsoid bundle
 */

namespace Trismegiste\ParsoidBundle\Contract;

use Wikimedia\Parsoid\Config\PageContent;

/**
 * Contract for providing wikitext template
 */
interface TemplateAccess
{
    /**
     * Gets a template by its name (or null if it's missing)
     * @param string $title
     * @return PageContent|null
     */
    public function fetchTemplate(string $title): ?PageContent;
}
