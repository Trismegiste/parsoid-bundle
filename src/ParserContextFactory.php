<?php

/*
 * ParsoidBundle
 */

namespace Trismegiste\ParsoidBundle;

use Trismegiste\ParsoidBundle\Internal\ParsoidPageConfig;
use Trismegiste\ParsoidBundle\Internal\ParsoidPageContent;
use Wikimedia\Parsoid\Config\DataAccess;
use Wikimedia\Parsoid\Config\Env;
use Wikimedia\Parsoid\Config\PageConfig;
use Wikimedia\Parsoid\Config\PageContent;
use Wikimedia\Parsoid\Config\SiteConfig;
use Wikimedia\Parsoid\Config\StubMetadataCollector;
use Wikimedia\Parsoid\Wt2Html\Frame;
use Wikimedia\Parsoid\Wt2Html\ParserPipeline;

/**
 * This is a factory for the creation of all objects co-operating for the parsoid parser.
 * Why multiple factories (SiteConfig, Parser, Context...) ?
 * You can have multiple SiteConfig in the container for each targeted device/app,
 * each config stores tag handlers and dom processors (specially for handling links).
 * And therefore, multiple Parser (for each SiteConfig) can coexist in the container.
 * On the other hand, PageConfig, PageContent... are different for each wikitext content. They aren't stored in the container.
 *
 * DP : Abstract Factory
 */
class ParserContextFactory
{
    public function __construct(
        protected SiteConfig $siteConfig,
        protected DataAccess $dataAccess,
        protected string $langague = 'en') {}

    /**
     * Creates the wikitext page content
     * @param string $wikitext
     * @return PageContent
     */
    public function createPageContent(string $wikitext): PageContent
    {
        return new ParsoidPageContent($wikitext);
    }

    /**
     * Creates the "page config", i.e the context for parsing a page : the page title, the language...
     * @param PageContent $pageContent
     * @param string $pageTitle
     * @return PageConfig
     */
    public function createPageConfig(PageContent $pageContent, string $pageTitle): PageConfig
    {
        return new ParsoidPageConfig($pageContent, $pageTitle, $this->langague);
    }

    /**
     * The infamous god-object Env acting as a poor man's DiC and creating tons of violations for the Hollywood Principle
     * @param PageConfig $cfg
     * @param type $opts
     * @return Env
     */
    public function createEnv(PageConfig $cfg, array $opts = []): Env
    {
        return new Env(
            $this->siteConfig,
            $cfg,
            $this->dataAccess,
            new StubMetadataCollector($this->siteConfig), // we don't GAF about metadata here
            $opts);
    }

    /**
     * Creates the pipeline ready to use. You can inject your own parameters replacing {{{younameit}}} in a template
     * @param Env $god
     * @param Frame|null $frame
     * @return ParserPipeline
     */
    public function createFullParserPipeline(Env $god, ?Frame $frame = null): ParserPipeline
    {
        // wikitext to html
        $pipelineFactory = $god->getPipelineFactory();
        $pipeline = $pipelineFactory->getPipeline('fullparse-wikitext-to-dom');
        $pipeline->init([
            'frame' => $frame ?? $god->topFrame,
            'toFragment' => false,
        ]);

        return $pipeline;
    }
}
