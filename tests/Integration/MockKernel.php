<?php

/*
 * ParsoidBundle
 */

namespace Trismegiste\ParsoidBundle\Tests\Integration;

use Override;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Trismegiste\ParsoidBundle\Tests\Mockup\PageMock;
use Trismegiste\ParsoidBundle\Tests\Mockup\PictureMock;
use Trismegiste\ParsoidBundle\Tests\Mockup\TemplateMock;
use Trismegiste\ParsoidBundle\TrismegisteParsoidBundle;

/**
 * AppKernel mockup for testing this bundle
 */
class MockKernel extends Kernel
{

    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new TrismegisteParsoidBundle();
    }

    private function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', ['test' => true, 'secret' => 'test']);
        $container->extension('trismegiste_parsoid', [
            'provider' => [
                'page' => PageMock::class,
                'picture' => PictureMock::class,
                'template' => TemplateMock::class
            ]
        ]);

        $container->import('../fixtures/services_mock.yaml');
    }

    #[Override]
    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/parsoid-bundle-test/cache/';
    }

    #[Override]
    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/parsoid-bundle-test/log/';
    }
}
