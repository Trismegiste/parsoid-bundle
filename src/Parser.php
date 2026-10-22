<?php

/*
 * ParsoidBundle
 */

namespace Trismegiste\ParsoidBundle;

use Wikimedia\Parsoid\Utils\ContentUtils;
use Wikimedia\Parsoid\Utils\DOMCompat;
use Wikimedia\Parsoid\Utils\DOMDataUtils;

/**
 * Facade for Parsoid
 */
class Parser
{
    const envOptions = [
        'wrapSections' => false,
        'nativeTemplateExpansion' => true,
        'skipLanguageConversionPass' => true
    ];

    public function __construct(protected ParserContextFactory $factory) {}

    public function parse(string $page, string $pageTitle = 'Wiki'): string
    {
        $pageContent = $this->factory->createPageContent($page);
        $pageConfig = $this->factory->createPageConfig($pageContent, $pageTitle);

        // building the environnement
        $env = $this->factory->createEnv($pageConfig, self::envOptions);

        $pipeline = $this->factory->createFullParserPipeline($env);
        $doc = $pipeline->parseChunkily($page, ['sol' => true])->ownerDocument;
        $body = DOMCompat::getBody($doc);

        // adding data-parsoid attributes
        DOMDataUtils::visitAndStoreDataAttribs($body, [
            'storeInPageBundle' => false,
            'outputContentVersion' => $env->getOutputContentVersion(),
        ]);

        return ContentUtils::toXML($body, ['innerXML' => true]);
    }
}
