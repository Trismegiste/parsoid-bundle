<?php

/*
 * Parsoid bundle
 */

namespace Trismegiste\ParsoidBundle\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\ExtensionTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Trismegiste\ParsoidBundle\TrismegisteParsoidBundle;

class TrismegisteParsoidBundleTest extends TestCase
{

    use ExtensionTrait;

    protected AbstractBundle $sut;

    protected function setUp(): void
    {
        $this->sut = new TrismegisteParsoidBundle();
    }

    public function testExtension()
    {
        $this->assertEquals('TrismegisteParsoidBundle', $this->sut->getName());

        $ext = $this->sut->getContainerExtension();
        $this->assertInstanceOf(Extension::class, $ext);
        $this->assertEquals('trismegiste_parsoid', $ext->getAlias());
    }

    public function getConfig()
    {
        return [
            ['config_1'],
            ['config_2']
        ];
    }

    /** @dataProvider getConfig */
    public function testCompileContainerWithConsumer($config)
    {
        // init the container like an app will do :
        $container = new ContainerBuilder();
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/fixtures'));
        // this yaml is a good example on how using this bundle
        $loader->load('services_mock.yaml');
        // some parameters for the configurator
        $container->setParameter('kernel.environment', 'test');
        $container->setParameter('kernel.build_dir', __DIR__);

        // add extension passes
        $this->sut->build($container);
        $container->registerExtension($this->sut->getContainerExtension());

        $callback = function (ContainerConfigurator $configurator) use ($config) {
            $configurator->import("../tests/fixtures/$config.yaml");
        };
        $this->executeConfiguratorCallback($container, $callback, $this->sut);
        // compile the ContainerBuilder, now we have instantiated services
        $container->compile();

        // tests on a consumer service of the parser
        $this->assertTrue($container->has('consumer'));
        $instantiated = $container->get('consumer');
        $this->assertStringStartsWith('<h2', $instantiated->parse('==Win=='));
    }
}
