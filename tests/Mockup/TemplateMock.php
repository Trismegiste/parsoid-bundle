<?php

/*
 * Parsoid bundle
 */

namespace Trismegiste\ParsoidBundle\Tests\Mockup;

use Override;
use Trismegiste\ParsoidBundle\Contract\TemplateAccess;
use Wikimedia\Parsoid\Config\PageContent;

class TemplateMock implements TemplateAccess
{
    #[Override]
    public function fetchTemplate(string $title): ?PageContent
    {
        return null;
    }
}
