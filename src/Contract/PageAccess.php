<?php

/*
 * Parsoid bundle
 */

namespace Trismegiste\ParsoidBundle\Contract;

/**
 * Contract for page repository
 */
interface PageAccess
{

    /**
     * Returns an array with keys as titles and values as found pks (or null if not found)
     */
    public function searchPkByTitle(array $title): array;
}
