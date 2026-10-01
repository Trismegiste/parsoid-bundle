<?php

/*
 * Parsoid bundle
 */

namespace Trismegiste\ParsoidBundle\PhpUnit;

use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;
use Trismegiste\ParsoidBundle\Contract\PageAccess;
use Trismegiste\ParsoidBundle\Contract\PictureAccess;
use Trismegiste\ParsoidBundle\Contract\TemplateAccess;
use Trismegiste\ParsoidBundle\Internal\DataAccess;
use Trismegiste\ParsoidBundle\LinkDefault;
use Trismegiste\ParsoidBundle\Parser;
use Trismegiste\ParsoidBundle\ParserFactory;
use Trismegiste\ParsoidBundle\SiteConfigFactory;
use Wikimedia\Parsoid\Ext\ExtensionTagHandler;

/**
 * Generic test case for testing tag handlers
 */
abstract class TagTestCase extends TestCase
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
        $scf = new SiteConfigFactory([$this->getTagName() => $this->createTag()]);
        $tsc = $scf->create('default', new LinkDefault());

        $this->sut = $fac->create($tsc);
        $this->crawler = new Crawler();
    }

    abstract protected function createTag(): ExtensionTagHandler;
    abstract protected function getTagName(): string;
}
