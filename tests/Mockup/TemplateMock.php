<?php

/*
 * Parsoid bundle
 */

namespace Trismegiste\ParsoidBundle\Tests\Mockup;

use Override;
use Trismegiste\ParsoidBundle\Contract\TemplateAccess;
use Trismegiste\ParsoidBundle\Internal\ParsoidPageContent;
use Wikimedia\Parsoid\Config\PageContent;

class TemplateMock implements TemplateAccess
{
    #[Override]
    public function fetchTemplate(string $title): ?PageContent
    {
        return new ParsoidPageContent(file_get_contents(__DIR__ . "/../fixtures/$title.wikitext"));
    }
}
