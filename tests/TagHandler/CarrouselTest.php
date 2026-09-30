<?php

/*
 * Parsoid bundle
 */

namespace Trismegiste\ParsoidBundle\Tests\TagHandler;

use Override;
use Trismegiste\ParsoidBundle\Contract\WikiFile;
use Trismegiste\ParsoidBundle\TagHandler\Carrousel;
use Wikimedia\Parsoid\Ext\ExtensionTagHandler;

class CarrouselTest extends TagTestCase
{
    #[Override]
    protected function createTag(): ExtensionTagHandler
    {
        return new Carrousel();
    }

    public function testEmptyCarrouselShort()
    {
        $ret = $this->sut->parse("<carrousel/>");
        $this->assertEmpty($ret);
    }

    public function testEmptyCarrousel()
    {
        $ret = $this->sut->parse("<carrousel></carrousel>");
        $this->assertEmpty($ret);
    }

    public function testInvalidOddCarrousel()
    {
        $ret = $this->sut->parse("<carrousel>Item</carrousel>");
        $this->assertStringContainsString('even number', $ret);
    }

    public function testInvalidEvenCarrouselPicture()
    {
        $ret = $this->sut->parse("<carrousel>Item\nItem</carrousel>");
        $this->assertStringContainsString('picture', $ret);
    }

    public function testInvalidEvenCarrouselCaption()
    {
        $ret = $this->sut->parse("<carrousel>[[file:lolcat1]]\n[[file:lolcat2]]</carrousel>");
        $this->assertStringContainsString('caption', $ret);
    }

    public function testValidCarrousel()
    {
        $pic = $this->createMock(WikiFile::class);
        $pic->expects($this->once())
                ->method('getUrl')
                ->willReturn('/view/lolcat');

        $this->picture->expects($this->once())
                ->method('searchInfoByTitle')
                ->willReturn([$pic]);

        $ret = $this->sut->parse("<carrousel>[[file:lolcat]]\nCaption</carrousel>");
        $this->assertStringStartsWith('<table', $ret);
        $this->crawler->addHtmlContent($ret);
        $this->assertCount(2, $this->crawler->filter('table tr'));
        $this->assertStringContainsString('/view/lolcat', $ret);
        $this->assertStringContainsString('Caption', $ret);
    }

    #[\Override]
    protected function getTagName(): string
    {
        return 'carrousel';
    }
}
