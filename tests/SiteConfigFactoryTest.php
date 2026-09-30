<?php

/*
 * ParsoidBundle
 */

namespace Trismegiste\ParsoidBundle\Tests;

use Override;
use PHPUnit\Framework\TestCase;
use Trismegiste\ParsoidBundle\Internal\DataAccess;
use Trismegiste\ParsoidBundle\LinkDefault;
use Trismegiste\ParsoidBundle\Parser;
use Trismegiste\ParsoidBundle\ParserFactory;
use Trismegiste\ParsoidBundle\SiteConfigFactory;
use Trismegiste\ParsoidBundle\TagHandler\Param;
use Wikimedia\Parsoid\Config\SiteConfig;

class SiteConfigFactoryTest extends TestCase
{
    protected SiteConfigFactory $sut;
    protected ParserFactory $fac;
    protected SiteConfig $tsc;

    #[Override]
    protected function setUp(): void
    {
        $data = $this->createStub(DataAccess::class);
        $this->fac = new ParserFactory($data);

        $this->sut = new SiteConfigFactory(tagList: ['param' => new Param()], interwiki: ['wp' => 'https://fr.wikipedia.org/wiki/$1']);
        $this->tsc = $this->sut->create('default', new LinkDefault());
    }

    public function testParserCreation()
    {
        $parser = $this->fac->create($this->tsc);
        $this->assertInstanceOf(Parser::class, $parser);
        $html = $parser->parse('=Yolo=');
        $this->assertStringContainsString('<h1', $html);
        $this->assertStringContainsString('Yolo', $html);
    }

    public function testParserCreationWithTag()
    {
        $parser = $this->fac->create($this->tsc);
        $html = $parser->parse('<param>toto:123</param>');
        $this->assertStringContainsString('<dl', $html);
        $this->assertStringContainsString('<dt', $html);
        $this->assertStringContainsString('<dd', $html);
        $this->assertStringContainsString('toto', $html);
    }

    public function testInterwikiMap()
    {
        $parser = $this->fac->create($this->tsc);
        $html = $parser->parse('[[wp:Blade Runner (film)]]');
        $this->assertStringContainsString('<a', $html);
        $this->assertStringContainsString('wikipedia', $html);
    }
}
