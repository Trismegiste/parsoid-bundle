<?php

/*
 * Parsoid bundle
 */

namespace Trismegiste\ParsoidBundle;

use Trismegiste\ParsoidBundle\Internal\DataAccess;
use Wikimedia\Parsoid\Config\SiteConfig;

/**
 * Creates Parser for different targets
 */
class ParserFactory
{
    protected array $extension = [];

    /**
     * Ctor
     * @param DataAccess $access The facade for accessing to page, picture and template
     */
    public function __construct(protected DataAccess $access) {}

    /**
     * Creates the parser for a given target
     * @param SiteConfig $target
     * @return Parser
     */
    public function create(SiteConfig $target): Parser
    {
        return new Parser($target, $this->access);
    }
}
