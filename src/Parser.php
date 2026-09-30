<?php

/*
 * ParsoidBundle
 */

namespace Trismegiste\ParsoidBundle;

use Trismegiste\ParsoidBundle\Internal\ParsoidPageConfig;
use Trismegiste\ParsoidBundle\Internal\ParsoidPageContent;
use Wikimedia\Parsoid\Config\DataAccess;
use Wikimedia\Parsoid\Config\Env;
use Wikimedia\Parsoid\Config\SiteConfig;
use Wikimedia\Parsoid\Config\StubMetadataCollector;
use Wikimedia\Parsoid\Ext\ParsoidExtensionAPI;
use Wikimedia\Parsoid\Utils\ContentUtils;
use Wikimedia\Parsoid\Utils\DOMCompat;
use Wikimedia\Parsoid\Utils\DOMDataUtils;

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

    public function __construct(
            protected SiteConfig $siteConfig,
            protected DataAccess $dataAccess,
            protected string $langague = 'en') {}

    public function parse(string $page, string $pageTitle = 'Wiki'): string
    {
        $pageContent = new ParsoidPageContent($page);
        $pageConfig = new ParsoidPageConfig($pageContent, $pageTitle, $this->langague);
        $metadata = new StubMetadataCollector($this->siteConfig);

        // building the environnement
        $env = new Env($this->siteConfig, $pageConfig, $this->dataAccess, $metadata, [
            'wrapSections' => false,
            'nativeTemplateExpansion' => true,
            'skipLanguageConversionPass' => true
        ]);

        // wikitext to html
        $contentModel = null;
        $handler = $env->getContentHandler($contentModel);
        $extApi = new ParsoidExtensionAPI($env);
        $doc = $handler->toDOM($extApi);
        $body = DOMCompat::getBody($doc);

        // adding data-parsoid attributes
        DOMDataUtils::visitAndStoreDataAttribs($body, [
            'storeInPageBundle' => false,
            'outputContentVersion' => $env->getOutputContentVersion(),
        ]);

        return ContentUtils::toXML($body, ['innerXML' => true]);
    }
}
