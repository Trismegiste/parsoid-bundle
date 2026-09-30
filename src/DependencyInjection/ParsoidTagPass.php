<?php

/*
 * Parsoid bundle
 */

namespace Trismegiste\ParsoidBundle\DependencyInjection;

use Override;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;
use Trismegiste\ParsoidBundle\SiteConfigFactory;
use Wikimedia\Parsoid\Ext\ExtensionTagHandler;

/**
 * Subscribing all parsoid.target tag to the parser factory
 */
class ParsoidTagPass implements CompilerPassInterface
{
    #[Override]
    public function process(ContainerBuilder $container): void
    {
        // search for parsoid targets
        $taggedServices = $container->findTaggedServiceIds('parsoid.tag');

        $convert = [];
        foreach ($taggedServices as $id => $tags) {
            // checking the tagged service
            $def = $container->getDefinition($id);
            if (!is_subclass_of($def->getClass(), ExtensionTagHandler::class, true)) {
                throw new LogicException("The service '$id' does not extend ExtensionTagHandler, it cannot be used as a tag for ParsoidBundle");
            }
            // getting the tag name used in ParserFactory
            foreach ($tags as $attributes) {
                $convert[$attributes['tag']] = new Reference($id);
            }
        }
        
        // inject tag handler extensions into the SiteConfig factory
        $fac = $container->getDefinition(SiteConfigFactory::class);
        $fac->setArgument('$tagList', $convert);
    }
}
