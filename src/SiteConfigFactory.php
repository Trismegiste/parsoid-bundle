<?php

/*
 * Aigor
 */

namespace Trismegiste\ParsoidBundle;

use Trismegiste\ParsoidBundle\Internal\TargetSiteConfig;
use Wikimedia\Parsoid\Config\SiteConfig;
use Wikimedia\Parsoid\Ext\ExtensionTagHandler;

/**
 * Factory for SiteConfig
 */
class SiteConfigFactory
{
    protected array $commonTag;

    public function __construct(array $tagList = [], protected array $interwiki = [])
    {
        // Creates the config for all extension tags (according to MediaWiki specs)
        $this->commonTag = [];
        foreach ($tagList as $tagName => $handler) {
            $this->commonTag[] = [
                'name' => $tagName,
                'handler' => ['factory' => static fn(): ExtensionTagHandler => $handler]
            ];
        }
    }

    /**
     * Creates a SiteConfig used by Parsoid
     * @param string $target
     * @param LinkOverride $linker
     * @return SiteConfig
     */
    public function create(string $target, LinkOverride $linker): SiteConfig
    {
        // MediaWiki specs for registering modules into a SiteConfig
        $extension = [
            'name' => "$target-bridge",
            'domProcessors' => [
                // the DOM post processor (aka the linker) transforms the links/media url from MediaWiki to Symfony
                ['factory' => static fn() => $linker]
            ],
            // and we include the extension tags config (gathered in the constructor)
            'tags' => $this->commonTag
        ];

        $instance = new TargetSiteConfig($this->interwiki);
        $instance->registerExtensionModule($extension);

        return $instance;
    }
}
