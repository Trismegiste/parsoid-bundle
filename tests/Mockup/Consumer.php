<?php

/*
 * Parsoid bundle
 */

namespace Trismegiste\ParsoidBundle\Tests\Mockup;

use Trismegiste\ParsoidBundle\Parser;

class Consumer
{

    public function __construct(protected Parser $parser)
    {
        
    }

    public function parse(string $str): string
    {
        return $this->parser->parse($str);
    }
}
