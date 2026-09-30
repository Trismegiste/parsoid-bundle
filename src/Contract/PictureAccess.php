<?php

/*
 * Parsoid bundle
 */

namespace Trismegiste\ParsoidBundle\Contract;

/**
 * Contract for access to picture
 */
interface PictureAccess
{
    /**
     * Returns an iterator over an ordered set of entries which can be either a WikiFile contract if the file exists or null if the file is missing
     * @param array $title
     * @return iterable
     */
    public function searchInfoByTitle(array $title): iterable;
}
