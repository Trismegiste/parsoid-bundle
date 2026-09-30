<?php

/*
 * Parsoid bundle
 */

namespace Trismegiste\ParsoidBundle\Tests\Internal;

use PHPUnit\Framework\TestCase;
use Trismegiste\ParsoidBundle\Contract\PageAccess;
use Trismegiste\ParsoidBundle\Contract\PictureAccess;
use Trismegiste\ParsoidBundle\Contract\TemplateAccess;
use Trismegiste\ParsoidBundle\Internal\DataAccess;
use Trismegiste\ParsoidBundle\Internal\ParsoidException;
use Wikimedia\Parsoid\Config\PageConfig;
use Wikimedia\Parsoid\Core\ContentMetadataCollector;

class DataAccessTest extends TestCase
{

    protected DataAccess $sut;
    protected PageAccess $repository;
    protected PictureAccess $storage;
    protected TemplateAccess $templateService;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(PageAccess::class);
        $this->storage = $this->createMock(PictureAccess::class);
        $this->templateService = $this->createMock(TemplateAccess::class);

        $this->sut = new DataAccess($this->repository, $this->storage, $this->templateService);
    }

    public function testPageInfo()
    {
        $this->repository->expects($this->once())
                ->method('searchPkByTitle')
                ->willReturn(['jupiter' => 123, 'Jupiter' => 123, 'Mars' => null]);

        $ret = $this->sut->getPageInfo('notused', ['jupiter', 'Jupiter', 'Mars']);
        $this->assertCount(3, $ret);
        $this->assertNotNull($ret['Jupiter']['pageId']);
        $this->assertNotNull($ret['jupiter']['pageId']);
        $this->assertNull($ret['Mars']['pageId']);
    }

    public function testParseWikitext()
    {
        $this->expectException(ParsoidException::class);
        $this->sut->parseWikitext($this->createStub(PageConfig::class), $this->createStub(ContentMetadataCollector::class), '');
    }

    public function testPreprocessWikitext()
    {
        $this->expectException(ParsoidException::class);
        $this->sut->preprocessWikitext($this->createStub(PageConfig::class), $this->createStub(ContentMetadataCollector::class), '');
    }
}
