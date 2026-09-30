<?php

/*
 * ParsoidBundle
 */

namespace Trismegiste\ParsoidBundle\Tests\TagHandler;

use Override;
use Trismegiste\ParsoidBundle\TagHandler\Param;
use Wikimedia\Parsoid\Ext\ExtensionTagHandler;

class ParamTest extends TagTestCase
{
    #[Override]
    protected function createTag(): ExtensionTagHandler
    {
        return new Param();
    }

    #[Override]
    protected function getTagName(): string
    {
        return 'param';
    }

    public function testEmptyParamShort()
    {
        $ret = $this->sut->parse("<param/>");
        $this->assertEmpty($ret);
    }

    public function testEmptyParam()
    {
        $ret = $this->sut->parse("<param></param>");
        $this->assertEmpty($ret);
    }

    public function testOnvalidParam()
    {
        $ret = $this->sut->parse("<param>data</param>");
        $this->crawler->addHtmlContent($ret);
        $this->assertCount(0, $this->crawler->filter('table tr'));
    }

    public function testOneParam()
    {
        $ret = $this->sut->parse("<param>yolo:42</param>");

        $this->assertStringStartsWith('<dl', $ret);
        $this->crawler->addHtmlContent($ret);
        $this->assertCount(1, $this->crawler->filter('dl dt'));
        $this->assertCount(1, $this->crawler->filter('dl dd'));
        $this->assertEquals('Yolo', $this->crawler->filter('dl dt')->text());
        $this->assertEquals(42, $this->crawler->filter('dl dd')->text());
    }
}
