<?php

/*
 * Parsoid bundle
 */

namespace Trismegiste\ParsoidBundle\Internal;

use Wikimedia\Parsoid\Core\LinkTarget;
use Wikimedia\Parsoid\Core\LinkTargetTrait;

/**
 * Page title
 */
class ParsoidLinkTarget implements LinkTarget
{

    use LinkTargetTrait;

    public function __construct(protected string $dbKey)
    {
        
    }

    public function createFragmentTarget(string $fragment): LinkTarget
    {
        //  return new self();
    }

    public function getDBkey(): string
    {
        return $this->dbKey;
    }

    public function getFragment(): string
    {
        return '';
    }

    public function getInterwiki(): string
    {
        return '';
    }

    public function getNamespace(): int
    {
        return 0;
    }
}
