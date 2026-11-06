<?php

/*
 * ParsoidBundle
 */

namespace Trismegiste\ParsoidBundle\Tests;

use Override;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Trismegiste\ParsoidBundle\Parser;

/**
 * Testing page properties with the extension tag handler PageProperty
 */
class PagePropertyTest extends KernelTestCase
{
    protected Parser $sut;
    protected Crawler $crawler;

    #[Override]
    protected function setUp(): void
    {
        $this->sut = static::getContainer()->get('parsoid.target.default');
        $this->crawler = new Crawler();
    }

    function testSimple()
    {
        $html = $this->sut->parse('===Wikitest===');
        $expected = <<<HTML
<h3 id="Wikitest" data-parsoid='{"dsr":[0,14,3,3]}'>Wikitest</h3>
HTML;
        $this->assertEquals($expected, $html);
    }

    function testPageProperty()
    {
        $html = $this->sut->parse('<prop>Yolo</prop>', pageProp: ['Yolo' => 42]);
        $this->crawler->addContent($html);
        $this->assertEquals('Yolo', $this->crawler->filter('dl dt')->text());
        $this->assertEquals('42', $this->crawler->filter('dl dd')->text());
    }
}
