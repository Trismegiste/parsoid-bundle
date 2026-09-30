<?php

/*
 * Parsoid bundle
 */

namespace Trismegiste\ParsoidBundle\Internal;

/**
 * Content of a wikitext Page
 */
class ParsoidPageContent extends \Wikimedia\Parsoid\Config\PageContent
{

    public function __construct(protected string $content)
    {
        
    }

    public function getContent(string $role): string
    {
        return $this->content;
    }

    public function getFormat(string $role): string
    {
        return 'text/x-wiki';
    }

    public function getModel(string $role): string
    {
        return 'wikitext';
    }

    public function getRoles(): array
    {
        return ['main'];
    }

    public function hasRole(string $role): bool
    {
        return 'main' === $role;
    }

}
