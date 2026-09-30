<?php

/*
 * Parsoid bundle
 */

namespace Trismegiste\ParsoidBundle\Tests\Internal;

use Override;
use PHPUnit\Framework\TestCase;
use Trismegiste\ParsoidBundle\Internal\ParsoidPageContent;

class ParsoidPageContentTest extends TestCase
{

    protected ParsoidPageContent $sut;

    #[Override]
    protected function setUp(): void
    {
        $this->sut = new ParsoidPageContent('pon pon pon');
    }

    public function testRoles()
    {
        $this->assertTrue($this->sut->hasRole('main'));
        $this->assertContains('main', $this->sut->getRoles());
    }

    public function testFormat()
    {
        $this->assertEquals('text/x-wiki', $this->sut->getFormat('main'));
        $this->assertEquals('wikitext', $this->sut->getModel('main'));
    }

    public function testContent()
    {
        $this->assertEquals('pon pon pon', $this->sut->getContent('main'));
    }
}
