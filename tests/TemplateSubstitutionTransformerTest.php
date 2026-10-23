<?php

/*
 * ParsoidBundle
 */

namespace Trismegiste\ParsoidBundle\Tests;

use Override;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Trismegiste\ParsoidBundle\TemplateSubstitutionTransformer;

class TemplateSubstitutionTransformerTest extends KernelTestCase
{
    protected TemplateSubstitutionTransformer $sut;

    #[Override]
    protected function setUp(): void
    {
        $this->sut = static::getContainer()->get(TemplateSubstitutionTransformer::class);
    }

    function testMultipleSubst()
    {
        $wikitext = "==Essai==\n{{subst:wip|Travail en cours}}\n* [[ luke | skywalker]]\n* leia\n{{subst:location|work=rebel}}";
        $result = $this->sut->replaceSubst($wikitext);

        $this->assertStringNotContainsString('<h2', $result);
        $this->assertStringContainsString('Travail', $result);
        $this->assertStringContainsString('<div', $result);
        $this->assertStringNotContainsString('<li', $result);
        $this->assertStringContainsString('<table', $result);
        $this->assertStringContainsString('[[rebel]]', $result); // links are kept as wikitext (this is the most important feature)
        $this->assertStringContainsString('[[ luke | skywalker]]', $result);
    }

    function testNoIncludeTag()
    {
        $wikitext = '{{noinclude}}';
        $parser = static::getContainer()->get('parsoid.target.default');
        $html = $parser->parse($wikitext);
        $this->assertStringContainsString('outside', $html);
        $this->assertStringNotContainsString('inside', $html);
    }

    function testMissingTemplate()
    {
        $wikitext = "{{subst:yolo|work=rebel}}";
        $result = $this->sut->replaceSubst($wikitext);
        $this->assertEquals($wikitext, $result);
    }

    function testWithExtensionTag()
    {
        $wikitext = "{{subst:param_tag}}";
        $result = $this->sut->replaceSubst($wikitext);
        $this->assertStringContainsString('158', $result);
        $this->assertStringContainsString('<param>', $result); // ensure the extension tag is not transform into HTML
    }
}
