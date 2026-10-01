<?php

/*
 * ParsoidBundle
 */

namespace Trismegiste\ParsoidBundle\PhpUnit;

/**
 * Data Provider for PhpUnit if you want to check consistent behavior in your own bundle
 */
trait DataProvider
{
    public function getExistingMedia(): array
    {
        return [
            ['[[file:lolcat]]'],
            ['[[ file:lolcat]]'],
            ['[[file:lolcat ]]'],
            ['[[file :lolcat]]'],
            ['[[file: lolcat]]'],
            ['[[ file: lolcat]]'],
            ['[[ file : lolcat ]]'],
            ['[[   file   :   lolcat   ]]']
        ];
    }

    public function getLinkWithSpecialChar(): array
    {
        return [
            ['Àvà'],
            ['Évé'],
            ['Çaça'],
            ['Æther'],
            ['Espacé titre'],
            ["Quo'ta'tion"]
        ];
    }
}
