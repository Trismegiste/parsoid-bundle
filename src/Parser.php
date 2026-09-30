<?php

/*
 * ParsoidBundle
 */

namespace Trismegiste\ParsoidBundle;

use Trismegiste\ParsoidBundle\Internal\ParsoidPageConfig;
use Trismegiste\ParsoidBundle\Internal\ParsoidPageContent;
use Wikimedia\Parsoid\Parsoid;

/**
 * Facade for Parsoid
 */
class Parser
{

    const parserOpts = [
        'body_only' => true,
        'wrapSections' => false,
        'discardDataParsoid' => true,
        'nativeTemplateExpansion' => true,
        'skipLanguageConversionPass' => true
    ];

    public function __construct(protected Parsoid $parsoid, protected string $langague = 'en')
    {
        
    }

    public function parse(string $page, string $pageTitle = 'Wiki'): string
    {
        $pageContent = new ParsoidPageContent($page);
        $pageConfig = new ParsoidPageConfig($pageContent, $pageTitle, $this->langague);

        return $this->parsoid->wikitext2html($pageConfig, self::parserOpts);
    }
}
