<?php

/*
 * ParsoidBundle
 */

namespace Trismegiste\ParsoidBundle\Tests\Mockup;

use Override;
use Wikimedia\Parsoid\Ext\ExtensionTagHandler;
use Wikimedia\Parsoid\Ext\ParsoidExtensionAPI;

/**
 * Tag extension handler who use the injected page properties.
 * This is a sample for tests, do not use this in prod (no check, no validation, no design).
 */
class PageProperty extends ExtensionTagHandler
{
    #[Override]
    public function sourceToDom(ParsoidExtensionAPI $extApi, string $src, array $extArgs)
    {
        $key = trim($src);
        $value = $extApi->getMetadata()->getPageProperty($key); // salacious downcasting to StubMetadataCollector, Metadata contract doesn't include getter methods

        $doc = $extApi->getTopLevelDoc();
        $table = $doc->createElement('dl');
        $fragment = $doc->createDocumentFragment();
        $fragment->appendChild($table);

        $table->appendChild($doc->createElement('dt', $key));
        $table->appendChild($doc->createElement('dd', $value));

        return $fragment;
    }
}
