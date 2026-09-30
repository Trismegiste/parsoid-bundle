<?php

/*
 * Parsoid bundle
 */

namespace Trismegiste\ParsoidBundle\Tests\Internal;

use Override;
use PHPUnit\Framework\TestCase;
use Trismegiste\ParsoidBundle\Internal\ParsoidException;
use Trismegiste\ParsoidBundle\Internal\TargetSiteConfig;

class TargetSiteConfigTest extends TestCase
{
    protected TargetSiteConfig $sut;

    #[Override]
    protected function setUp(): void
    {
        $this->sut = new TargetSiteConfig([]);
    }

    public function testNotImplemented1()
    {
        $this->assertEquals([], $this->sut->allowedExternalImagePrefixes());
    }

    public function testNotImplemented2()
    {
        $this->expectException(ParsoidException::class);
        $this->sut->bswRegexp();
    }

    public function testNotImplemented3()
    {
        $this->expectException(ParsoidException::class);
        $this->sut->categoryRegexp();
    }

    public function testNotImplemented4()
    {
        $this->expectException(ParsoidException::class);
        $this->sut->interwikiMagic();
    }

    public function testNotImplemented5()
    {
        $this->expectException(ParsoidException::class);
        $this->sut->iwp();
    }

    public function testNotImplemented6()
    {
        $this->expectException(ParsoidException::class);
        $this->sut->langBcp47();
    }

    public function testNotImplemented7()
    {
        $this->expectException(ParsoidException::class);
        $this->sut->redirectRegexp();
    }

    public function testNotImplemented8()
    {
        $this->expectException(ParsoidException::class);
        $this->sut->rtl();
    }

    public function testNotImplemented9()
    {
        $this->expectException(ParsoidException::class);
        $this->sut->script();
    }

    public function testNotImplementedA()
    {
        $this->expectException(ParsoidException::class);
        $this->sut->scriptpath();
    }

    public function testNotImplementedB()
    {
        $this->expectException(ParsoidException::class);
        $this->sut->server();
    }

    public function testNotImplementedC()
    {
        $this->expectException(ParsoidException::class);
        $this->sut->timezoneOffset();
    }

    public function testNotImplementedD()
    {
        $this->expectException(ParsoidException::class);
        $this->sut->widthOption();
    }
}
