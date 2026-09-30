<?php

/*
 * Parsoid bundle
 */

namespace Trismegiste\ParsoidBundle;

use Override;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Trismegiste\ParsoidBundle\DependencyInjection\ParsoidTagPass;
use Trismegiste\ParsoidBundle\DependencyInjection\ParsoidTargetPass;
use Trismegiste\ParsoidBundle\Internal\DataAccess;

/**
 * Bundle Parsoid
 */
class TrismegisteParsoidBundle extends AbstractBundle
{

    #[Override]
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // default service for this bundle
         $container->import('../config/services.yaml');

        // inject providers
        $access = $builder->getDefinition(DataAccess::class);
        $provider = $config['provider'];
        $access->setArgument(0, new Reference($provider['page']));
        $access->setArgument(1, new Reference($provider['picture']));
        $access->setArgument(2, new Reference($provider['template']));

        // inject the interwiki map in the SiteConfigFactory
        $fac = $builder->getDefinition(SiteConfigFactory::class);
        $fac->setArgument('$interwiki', $config['interwiki']);
    }

    #[\Override]
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new ParsoidTargetPass());
        $container->addCompilerPass(new ParsoidTagPass());
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
                ->children()
                    ->arrayNode('provider')
                        ->info('All data providers needed for rendering wikitext pages')
                        ->children()
                            ->scalarNode('page')
                                ->isRequired()
                                ->info('Provider for page content in wikitext format (implementing PageAccess contract)')
                            ->end()
                            ->scalarNode('picture')
                                ->isRequired()
                                ->info('Provider for pictures (implementing PictureAccess contract)')
                            ->end()
                            ->scalarNode('template')
                                ->isRequired()
                                ->info('Provider for wikitext templates (implementing TemplateAccess contract)')
                            ->end()
                        ->end()
                    ->end() // end provider
                    ->arrayNode('interwiki')
                        ->info("Array of interwiki configuration in the format 'namespace' => 'URL with $1 as placeholder'")
                        ->defaultValue([])
                        ->useAttributeAsKey('key')
                        ->scalarPrototype()->end()
                    ->end() // end interwiki
                ->end() // end children
        ;
    }
}
