<?php

/*
 * ParsoidBundle
 */

namespace Trismegiste\ParsoidBundle;

use Wikimedia\Parsoid\Config\Env;
use Wikimedia\Parsoid\Config\PageConfig;
use Wikimedia\Parsoid\Config\PageContent;
use Wikimedia\Parsoid\Wt2Html\Frame;
use Wikimedia\Parsoid\Wt2Html\ParserPipeline;

/**
 * This is a factory for the creation of all objects co-operating for the parsoid parser.
 *
 * Why multiple factories (for SiteConfig, for Parser, for Context...) ?
 * You can have multiple SiteConfig in the container for each targeted device/app,
 * each config stores tag handlers and dom processors (specially for handling links).
 * And therefore, multiple Parser (for each SiteConfig) can coexist in the container.
 * On the other hand, PageConfig, PageContent... are different for each wikitext content. They can't be stored in the container.
 *
 * DP : Abstract Factory
 */
interface ParserContextAbstractFactory
{
    /**
     * Creates the wikitext page content
     * @param string $wikitext
     * @return PageContent
     */
    public function createPageContent(string $wikitext): PageContent;
    /**
     * Creates the "page config", i.e the context for parsing a page : the page title, the language...
     * @param PageContent $pageContent
     * @param string $pageTitle
     * @return PageConfig
     */
    public function createPageConfig(PageContent $pageContent, string $pageTitle): PageConfig;
    /**
     * The infamous god-object Env acting as a poor man's DiC and creating tons of violations for the Hollywood Principle
     * @param PageConfig $cfg
     * @param type $opts Options for Env
     * @param array $pageProp Page properties
     * @return Env
     */
    public function createEnv(PageConfig $cfg, array $opts = [], array $pageProp = []): Env;
    /**
     * Creates the pipeline ready to use. You can inject your own parameters replacing {{{younameit}}} in a template
     * @param Env $god
     * @param Frame|null $frame
     * @return ParserPipeline
     */
    public function createFullParserPipeline(Env $god, ?Frame $frame = null): ParserPipeline;
}
