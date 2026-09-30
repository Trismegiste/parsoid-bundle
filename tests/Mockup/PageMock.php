<?php

/*
 * Parsoid bundle
 */

namespace Trismegiste\ParsoidBundle\Tests\Mockup;

use Trismegiste\ParsoidBundle\Contract\PageAccess;

class PageMock implements PageAccess
{

    public function searchPkByTitle(array $title): array
    {
        return [];
    }
}
