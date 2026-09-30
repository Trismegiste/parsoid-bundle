<?php

/*
 * Parsoid bundle
 */

namespace Trismegiste\ParsoidBundle\DependencyInjection;

use Override;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;
use Trismegiste\ParsoidBundle\Internal\TargetSiteConfig;
use Trismegiste\ParsoidBundle\LinkOverride;
use Trismegiste\ParsoidBundle\Parser;
use Trismegiste\ParsoidBundle\ParserFactory;
use Trismegiste\ParsoidBundle\SiteConfigFactory;

/**
 * Subscribing all parsoid.target tag to the parser factory
 */
class ParsoidTargetPass implements CompilerPassInterface
{
    #[Override]
    public function process(ContainerBuilder $container): void
    {
        // search for parsoid targets
        $taggedServices = $container->findTaggedServiceIds('parsoid.target');

        // gather target configurations from the tagged concrete LinkOverride implementations
        $convert = [];
        foreach ($taggedServices as $id => $tags) {
            // checking the tagged service
            $def = $container->getDefinition($id);
            if (!is_subclass_of($def->getClass(), LinkOverride::class, true)) {
                throw new LogicException("The service '$id' does not extend LinkOverride, it cannot be used as a target for ParsoidBundle");
            }
            // getting the target name use in ParserFactory
            foreach ($tags as $attributes) {
                if (!key_exists('target', $attributes)) {
                    throw new LogicException("The service '$id' has a 'parsoid.target' tag but the attribute 'target' is missing");
                }
                $convert[$attributes['target']] = new Reference($id);
            }
        }

        // For each target configuration :
        foreach ($convert as $target => $linker) {
            // Declare a TargetSiteConfig :
            $tsc = new Definition(TargetSiteConfig::class);
            $tsc->setFactory([new Reference(SiteConfigFactory::class), 'create']);
            $tsc->setArgument('$target', $target);
            $tsc->setArgument('$linker', $linker);
            $container->setDefinition("parsoid.siteconfig.$target", $tsc);

            // Declare a Parser service :
            $parser = new Definition(Parser::class);
            $parser->setFactory([new Reference(ParserFactory::class), 'create']);
            $parser->setArgument('$target', new Reference("parsoid.siteconfig.$target"));
            $container->setDefinition("parsoid.target.$target", $parser);
        }
    }
}
