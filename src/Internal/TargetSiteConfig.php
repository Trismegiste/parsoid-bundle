<?php

/*
 * Parsoid bundle
 */

namespace Trismegiste\ParsoidBundle\Internal;

use Override;
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

    #[Override]
    protected function getMagicWords(): array
    {
        return [];
    }

    #[Override]
    protected function getNonNativeExtensionTags(): array
    {
        return [];
    }

    #[Override]
    protected function getParameterizedAliasMatcher(array $words): callable
    {
        return function ($assoc) {
            
        };
    }

    #[Override]
    protected function getProtocols(): array
    {
        return ["http:", "https:"];
    }

    #[Override]
    protected function getSpecialNSAliases(): array
    {
        return [];
    }

    #[Override]
    protected function getSpecialPageAliases(string $specialPage): array
    {
        return [];
    }

    #[Override]
    protected function getVariableIDs(): array
    {
        return []; // None for now
    }

    #[Override]
    protected function linkTrail(): string
    {
        return '';
    }

    #[Override]
    public function allowedExternalImagePrefixes(): array
    {
        return [];
    }

    #[Override]
    public function baseURI(): string
    {
        return 'yolo'; // this value is overriden by the pair of Link DOMProcessors
    }

    #[Override]
    public function bswRegexp(): string
    {
        return '#__MOCK__#';
    }

    #[Override]
    public function canonicalNamespaceId(string $name): ?int
    {
        return $this->namespaceMap[$name] ?? null;
    }

    #[Override]
    public function categoryRegexp(): string
    {
        return '#__CATEGROY__#';
    }

    #[Override]
    public function exportMetadataToHeadBcp47(Document $document, ContentMetadataCollector $metadata, string $defaultTitle, Bcp47Code $lang): void {}

    #[Override]
    public function getExternalLinkTarget(): string|false
    {
        return '_blank';
    }

    #[Override]
    public function getMWConfigValue(string $key): mixed
    {
        return null;
    }

    #[Override]
    public function getMagicWordMatcher(string $id): string
    {
        return '/(?!)/';
    }

    #[Override]
    public function getMaxTemplateDepth(): int
    {
        return 40;
    }

    #[Override]
    public function getNoFollowConfig(): array
    {
        return ['nofollow' => true,
            'nsexceptions' => [1],
            'domainexceptions' => ['mediawiki.org']
        ];
    }

    #[Override]
    public function interwikiMagic(): bool
    {
        throw new ParsoidException('Not implemented (should not be called)');
    }

    #[Override]
    public function interwikiMap(): array
    {
        return $this->interwikiMap;
    }

    #[Override]
    public function iwp(): string
    {
        throw new ParsoidException('Not implemented (should not be called)');
    }

    #[Override]
    public function langBcp47(): Bcp47Code
    {
        throw new ParsoidException('Not implemented (should not be called)');
    }

    #[Override]
    public function langConverterEnabledBcp47(Bcp47Code $lang): bool
    {
        return false;
    }

    #[Override]
    public function legalTitleChars(): string
    {
        return ' %!"$&\'()*,\-.\/0-9:;=?@A-Z\\\\^_`a-z~\x80-\xFF+';
    }

    #[Override]
    public function linkPrefixRegex(): ?string
    {
        return null;
    }

    #[Override]
    public function mainPageLinkTarget(): LinkTarget
    {
        return new ParsoidLinkTarget('Main Page');
    }

    #[Override]
    public function namespaceCase(int $ns): string
    {
        return 'case-sensitive';
    }

    #[Override]
    public function namespaceHasSubpages(int $ns): bool
    {
        return false;
    }

    public function namespaceId(string $name): ?int
    {
        return null;
    }

    #[Override]
    public function namespaceName(int $ns): ?string
    {
        return array_search($ns, $this->namespaceMap);
    }

    #[Override]
    public function redirectRegexp(): string
    {
        return '#__REDIRECT__#';
    }

    #[Override]
    public function rtl(): bool
    {
        throw new ParsoidException('Not implemented (should not be called)');
    }

    #[Override]
    public function script(): string
    {
        throw new ParsoidException('Not implemented (should not be called)');
    }

    #[Override]
    public function scriptpath(): string
    {
        throw new ParsoidException('Not implemented (should not be called)');
    }

    #[Override]
    public function server(): string
    {
        throw new ParsoidException('Not implemented (should not be called)');
    }

    #[Override]
    public function specialPageLocalName(string $alias): ?string {}

    #[Override]
    public function timezoneOffset(): int
    {
        throw new ParsoidException('Not implemented (should not be called)');
    }

    #[Override]
    public function variantsFor(Bcp47Code $lang): ?array {}

    #[Override]
    public function widthOption(): int
    {
        throw new ParsoidException('Not implemented (should not be called)');
    }

    #[Override]
    public function incrementCounter(string $name, array $labels, float $amount = 1): void {}

    #[Override]
    public function observeTiming(string $name, float $value, array $labels): void {}
}
