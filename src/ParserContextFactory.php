<?php

/*
 * ParsoidBundle
 */

namespace Trismegiste\ParsoidBundle;

use Override;
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
 * Implementation of ParserContextAbstractFactory
 * (no comment here because LSP)
 */
class ParserContextFactory implements ParserContextAbstractFactory
{
    public function __construct(
        protected SiteConfig $siteConfig,
        protected DataAccess $dataAccess,
        protected string $langague = 'en') {}

    #[Override]
    public function createPageContent(string $wikitext): PageContent
    {
        return new ParsoidPageContent($wikitext);
    }

    #[Override]
    public function createPageConfig(PageContent $pageContent, string $pageTitle): PageConfig
    {
        return new ParsoidPageConfig($pageContent, $pageTitle, $this->langague);
    }

    #[Override]
    public function createEnv(PageConfig $cfg, array $opts = [], array $pageProp = []): Env
    {
        $meta = new StubMetadataCollector($this->siteConfig); // dumb generic metadata collector
        // Inject page properties into the metadata
        foreach ($pageProp as $k => $v) {
            $meta->setUnsortedPageProperty($k, $v);
        }

        return new Env(
            $this->siteConfig,
            $cfg,
            $this->dataAccess,
            $meta,
            $opts);
    }

    #[Override]
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
