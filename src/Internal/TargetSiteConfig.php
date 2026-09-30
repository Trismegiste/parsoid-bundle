<?php

/*
 * Parsoid bundle
 */

namespace Trismegiste\ParsoidBundle\Internal;

use Wikimedia\Bcp47Code\Bcp47Code;
use Wikimedia\Parsoid\Config\SiteConfig;
use Wikimedia\Parsoid\Core\ContentMetadataCollector;
use Wikimedia\Parsoid\Core\LinkTarget;
use Wikimedia\Parsoid\DOM\Document;

/**
 * Configuration of the internal wiki site for Parsoid
 */
class TargetSiteConfig extends SiteConfig
{
    protected array $interwikiMap = [];
    protected $namespaceMap = [
        '' => 0,
        'file' => 6,
        'template' => 7
    ];

    public function __construct(array $interwikiMap)
    {
        parent::__construct();

        // initialising interwiki map
        foreach ($interwikiMap as $key => $url) {
            $this->interwikiMap[$key] = [
                'prefix' => $key,
                'url' => $url
            ];
        }
    }

    protected function getMagicWords(): array
    {
        return [];
    }

    protected function getNonNativeExtensionTags(): array
    {
        return [];
    }

    protected function getParameterizedAliasMatcher(array $words): callable
    {
        return function ($assoc) {
            
        };
    }

    protected function getProtocols(): array
    {
        return ["http:", "https:"];
    }

    protected function getSpecialNSAliases(): array
    {
        
    }

    protected function getSpecialPageAliases(string $specialPage): array
    {
        
    }

    protected function getVariableIDs(): array
    {
        return []; // None for now
    }

    protected function linkTrail(): string
    {
        return '';
    }

    public function allowedExternalImagePrefixes(): array
    {
        return [];
    }

    public function baseURI(): string
    {
        return 'yolo'; // this value is overriden by the pair of Link DOMProcessors
    }

    public function bswRegexp(): string
    {
        throw new ParsoidException('Not implemented (should not be called');
    }

    public function canonicalNamespaceId(string $name): ?int
    {
        return $this->namespaceMap[$name] ?? null;
    }

    public function categoryRegexp(): string
    {
        throw new ParsoidException('Not implemented (should not be called');
    }

    public function exportMetadataToHeadBcp47(Document $document, ContentMetadataCollector $metadata, string $defaultTitle, Bcp47Code $lang): void
    {
        
    }

    public function getExternalLinkTarget(): string|false
    {
        return '_blank';
    }

    public function getMWConfigValue(string $key): mixed
    {
        return null;
    }

    public function getMagicWordMatcher(string $id): string
    {
        return '/(?!)/';
    }

    public function getMaxTemplateDepth(): int
    {
        return 40;
    }

    public function getNoFollowConfig(): array
    {
        return ['nofollow' => true,
            'nsexceptions' => [1],
            'domainexceptions' => ['mediawiki.org']
        ];
    }

    public function interwikiMagic(): bool
    {
        throw new ParsoidException('Not implemented (should not be called');
    }

    public function interwikiMap(): array
    {
        return $this->interwikiMap;
    }

    public function iwp(): string
    {
        throw new ParsoidException('Not implemented (should not be called');
    }

    public function langBcp47(): Bcp47Code
    {
        throw new ParsoidException('Not implemented (should not be called');
    }

    public function langConverterEnabledBcp47(Bcp47Code $lang): bool
    {
        return false;
    }

    public function legalTitleChars(): string
    {
        return ' %!"$&\'()*,\-.\/0-9:;=?@A-Z\\\\^_`a-z~\x80-\xFF+';
    }

    public function linkPrefixRegex(): ?string
    {
        return null;
    }

    public function mainPageLinkTarget(): LinkTarget
    {
        return new ParsoidLinkTarget('Main Page');
    }

    public function namespaceCase(int $ns): string
    {
        return 'case-sensitive';
    }

    public function namespaceHasSubpages(int $ns): bool
    {
        return false;
    }

    public function namespaceId(string $name): ?int
    {
        return null;
    }

    public function namespaceName(int $ns): ?string
    {
        return array_search($ns, $this->namespaceMap);
    }

    public function redirectRegexp(): string
    {
        throw new ParsoidException('Not implemented (should not be called');
    }

    public function rtl(): bool
    {
        throw new ParsoidException('Not implemented (should not be called');
    }

    public function script(): string
    {
        throw new ParsoidException('Not implemented (should not be called');
    }

    public function scriptpath(): string
    {
        throw new ParsoidException('Not implemented (should not be called');
    }

    public function server(): string
    {
        throw new ParsoidException('Not implemented (should not be called');
    }

    public function specialPageLocalName(string $alias): ?string
    {
        
    }

    public function timezoneOffset(): int
    {
        throw new ParsoidException('Not implemented (should not be called');
    }

    public function variantsFor(Bcp47Code $lang): ?array
    {
        
    }

    public function widthOption(): int
    {
        throw new ParsoidException('Not implemented (should not be called');
    }

    public function incrementCounter(string $name, array $labels, float $amount = 1): void
    {
        
    }

    public function observeTiming(string $name, float $value, array $labels): void
    {
        
    }
}
