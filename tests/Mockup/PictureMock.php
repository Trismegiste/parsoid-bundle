<?php

/*
 * Parsoid bundle
 */

namespace Trismegiste\ParsoidBundle\Tests\Mockup;

use Trismegiste\ParsoidBundle\Contract\PictureAccess;

class PictureMock implements PictureAccess
{

    public function searchInfoByTitle(array $title): iterable
    {
        return [];
    }
}
