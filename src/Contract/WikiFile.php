<?php

/*
 * Parsoid bundle
 */

namespace Trismegiste\ParsoidBundle\Contract;

/**
 * a file used by parsoid
 */
interface WikiFile
{

    public function getHeight(): int;

    public function getWidth(): int;

    public function getMimeType(): string;

    public function getUrl(): string;
}
