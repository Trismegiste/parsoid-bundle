<?php

/*
 * Parsoid bundle
 */

namespace Trismegiste\ParsoidBundle\Tests;

use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;
use Trismegiste\ParsoidBundle\Contract\PageAccess;
use Trismegiste\ParsoidBundle\Contract\PictureAccess;
use Trismegiste\ParsoidBundle\Contract\TemplateAccess;
use Trismegiste\ParsoidBundle\Contract\WikiFile;
use Trismegiste\ParsoidBundle\Internal\DataAccess;
use Trismegiste\ParsoidBundle\Internal\ParsoidPageContent;
use Trismegiste\ParsoidBundle\Internal\TargetSiteConfig;
use Trismegiste\ParsoidBundle\Parser;
use Trismegiste\ParsoidBundle\ParserFactory;

class ParserTest extends TestCase
{
    protected Parser $sut;
    protected Crawler $crawler;
    protected MockObject $page;
    protected MockObject $picture;
    protected MockObject $template;

    #[Override]
    protected function setUp(): void
    {
        $this->page = $this->createMock(PageAccess::class);
        $this->picture = $this->createMock(PictureAccess::class);
        $this->template = $this->createMock(TemplateAccess::class);

        $access = new DataAccess($this->page, $this->picture, $this->template);
        $fac = new ParserFactory($access);

        $this->sut = $fac->create(new TargetSiteConfig([]));
        $this->crawler = new Crawler();
    }

    public function testParagraph()
    {
        $html = $this->sut->parse('Hello World!');
        $this->assertEquals("<p data-parsoid='{\"dsr\":[0,12,0,0]}'>Hello World!</p>", $html);
    }

    public function testTitle()
    {
        $html = $this->sut->parse('==Titre==');
        $this->assertEquals("<h2 id=\"Titre\" data-parsoid='{\"dsr\":[0,9,2,2]}'>Titre</h2>", $html);
    }

    public function testQuote()
    {
        $html = $this->sut->parse(' Bloc');
        $this->assertEquals("<pre data-parsoid='{\"dsr\":[0,5,1,0]}'>Bloc</pre>", $html);
    }

    public function testList()
    {
        $html = $this->sut->parse('* item');
        $this->assertEquals("<ul data-parsoid='{\"dsr\":[0,6,0,0]}'><li data-parsoid='{\"dsr\":[0,6,1,0,1,0]}'>item</li></ul>", $html);
    }

    public function testBold()
    {
        $html = $this->sut->parse("'''hilite'''");
        $this->assertEquals("<p data-parsoid='{\"dsr\":[0,12,0,0]}'><b data-parsoid='{\"dsr\":[0,12,3,3]}'>hilite</b></p>", $html);
    }

    public function testItalic()
    {
        $html = $this->sut->parse("''hilite''");
        $this->assertEquals("<p data-parsoid='{\"dsr\":[0,10,0,0]}'><i data-parsoid='{\"dsr\":[0,10,2,2]}'>hilite</i></p>", $html);
    }

    public function getRedlink(): array
    {
        return [
            ['[[missing]]'],
            ['[[ missing]]'],
            ['[[missing ]]'],
            ['[[ missing ]]']
        ];
    }

    /** @dataProvider getRedlink */
    public function testRedLink(string $link)
    {
        $this->page->expects($this->once())
                ->method('searchPkByTitle')
                ->willReturn(['missing' => null]);

        $html = $this->sut->parse($link);
        $this->crawler->addHtmlContent($html);
        $this->assertEquals('./missing?action=edit&redlink=1', $this->crawler->filter('a')->attr('href'));
    }

    public function getLinkExist(): array
    {
        return [
            ['[[exist]]'],
            ['[[ exist]]'],
            ['[[exist ]]'],
            ['[[ exist ]]']
        ];
    }

    /** @dataProvider getLinkExist */
    public function testExistingLink(string $link)
    {
        $this->page->expects($this->once())
                ->method('searchPkByTitle')
                ->willReturn(['exist' => 123]);

        $html = $this->sut->parse($link);
        $this->crawler->addHtmlContent($html);
        $this->assertEquals('./exist', $this->crawler->filter('a')->attr('href'));
    }

    static public function getExistingMedia(): array
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

    /** @dataProvider getExistingMedia */
    public function testExistingPicture($link)
    {
        $pic = $this->createMock(WikiFile::class);
        $pic->expects($this->once())
                ->method('getUrl')
                ->willReturn('/view/lolcat');

        $this->picture->expects($this->once())
                ->method('searchInfoByTitle')
                ->willReturn([$pic]);

        $html = $this->sut->parse($link);
        $this->crawler->addHtmlContent($html);
        $this->assertEquals('mw:File', $this->crawler->filter('span')->attr('typeof'));
        $this->assertEquals('/view/lolcat', $this->crawler->filter('img')->attr('src'));
        $this->assertEquals('./file:lolcat', $this->crawler->filter('img')->attr('resource'));
        $dp = json_decode($this->crawler->filter('img')->attr('data-parsoid'), true);
        $this->assertEquals('./file:lolcat', $dp['a']['resource']);
    }

    public function testMissingPicture()
    {
        $this->picture->expects($this->once())
                ->method('searchInfoByTitle')
                ->willReturn([null]);

        $html = $this->sut->parse("[[file:missing]]");
        $this->crawler->addHtmlContent($html);
        $this->assertEquals('mw:Error mw:File', $this->crawler->filter('span')->attr('typeof'));
        $this->assertEquals('./Special:FilePath/missing', $this->crawler->filter('a')->attr('href'));
    }

    public function testTemplate()
    {
        $this->template->expects($this->once())
                ->method('fetchTemplate')
                ->willReturn(new ParsoidPageContent('coucou'));

        $html = $this->sut->parse("{{sample}}");
        $this->crawler->addHtmlContent($html);
        $this->assertEquals('mw:Transclusion', $this->crawler->filter('span')->attr('typeof'));
        $this->assertEquals('coucou', $this->crawler->filter('span')->text());
    }

    public function testTable()
    {
        $html = $this->sut->parse("{|\n|yolo\n|}");
        $this->crawler->addHtmlContent($html);
        $this->assertEquals('yolo', $this->crawler->filter('table tbody tr td')->text());
    }

    public function getLink(): array
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

    /**
     * @dataProvider getLink
     */
    public function testSpecialCharacter(string $title)
    {
        $html = $this->sut->parse("Lien vers [[$title]]");
        $this->crawler->addHtmlContent($html);
        $link = $this->crawler->filter('a');
        $this->assertCount(1, $link);
        $this->assertEquals($title, $link->text());
        $this->assertEquals('./' . str_replace(' ', '_', $title), $this->crawler->filter('a')->attr('href'));
    }

    /**
     * @dataProvider getLink
     */
    public function testSpecialCharacterWithName(string $title)
    {
        $html = $this->sut->parse("Lien vers [[$title|lien]]");
        $this->crawler->addHtmlContent($html);
        $link = $this->crawler->filter('a');
        $this->assertCount(1, $link);
        $this->assertEquals('lien', $link->text());
        $this->assertEquals('./' . str_replace(' ', '_', $title), $this->crawler->filter('a')->attr('href'));
    }

    function testExternalLink()
    {
        $html = $this->sut->parse("Les troyens : [https://www.youtube.com/watch?v=Bn2A2TOHzg8]");
        $this->assertStringContainsString('href', $html);
    }
}
