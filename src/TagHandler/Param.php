<?php

/*
 * ParsoidBundle
 */

namespace Trismegiste\ParsoidBundle\TagHandler;

use Wikimedia\Parsoid\DOM\DocumentFragment;
use Wikimedia\Parsoid\Ext\ExtensionTagHandler;
use Wikimedia\Parsoid\Ext\ParsoidExtensionAPI;
use const MB_CASE_TITLE;
use function mb_convert_case;

/**
 * Parameters sheet embedded in a <param> tag. A collection of key-values.
 * * Each line inside the tag is treated as a parameter
 * * The parameter name and the parameter value must be separated by a colon
 * * Spaces are trimmed and the parameter name is capitalized
 */
class Param extends ExtensionTagHandler
{

    public function sourceToDom(ParsoidExtensionAPI $extApi, string $src, array $extArgs): DocumentFragment|false|null
    {
        if (empty($src)) {
            return null;
        }

        $parameter = explode("\n", trim($src));

        $doc = $extApi->getTopLevelDoc();
        $table = $doc->createElement('dl');
        $table->setAttribute('class', 'param-infobox');
        $fragment = $doc->createDocumentFragment();
        $fragment->appendChild($table);

        foreach ($parameter as $row) {
            $param = explode(':', $row);
            if (count($param) !== 2) {
                continue;
            }
            $table->appendChild($doc->createElement('dt', mb_convert_case(trim($param[0]), MB_CASE_TITLE,)));
            $table->appendChild($doc->createElement('dd', trim($param[1])));
        }

        return $fragment;
    }
}
