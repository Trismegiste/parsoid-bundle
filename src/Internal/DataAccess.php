<?php

/*
 * Parsoid bundle
 */

namespace Trismegiste\ParsoidBundle\Internal;

use Override;
use Trismegiste\ParsoidBundle\Contract\PageAccess;
use Trismegiste\ParsoidBundle\Contract\PictureAccess;
use Trismegiste\ParsoidBundle\Contract\TemplateAccess;
use Trismegiste\ParsoidBundle\Contract\WikiFile;
use Wikimedia\Parsoid\Config\DataAccess as BaseAccess;
use Wikimedia\Parsoid\Config\PageConfig;
use Wikimedia\Parsoid\Config\PageContent;
use Wikimedia\Parsoid\Core\ContentMetadataCollector;
use Wikimedia\Parsoid\Core\LinkTarget;

/**
 * Repository for MediaWiki bridging with a page repository, a storage repository and a template repository
 */
class DataAccess extends BaseAccess
{
    public function __construct(
            protected PageAccess $repository,
            protected PictureAccess $storage,
            protected TemplateAccess $templateService) {}

    public function fetchTemplateData(PageConfig $pageConfig, LinkTarget $title): ?array {}

    /**
     * Returns a template content by its name (cached)
     * @param PageConfig $pageConfig
     * @param LinkTarget $title
     * @return PageContent|null
     */
    #[Override]
    public function fetchTemplateSource(PageConfig $pageConfig, LinkTarget $title): ?PageContent
    {
        return $this->templateService->fetchTemplate($title->getDBkey());
    }

    /**
     * Returns an array of informations for a given list of pictures, by name
     * @param PageConfig $pageConfig
     * @param array $files
     * @return array
     */
    #[Override]
    public function getFileInfo(PageConfig $pageConfig, array $files): array
    {
        $title = array_map(function (array $entry): string {
            // monkey patching : somewhere in parsoid, spaces in filenames are replaced by underscores and it's not overridable.
            // Here I revert the process by replacing underscores with spaces but this means that,
            // if there were underscores in the original filename, they are lost.
            // That's why underscores are forbidden in titles for pages and pictures (and tbh it's ugly)
            return str_replace('_', ' ', $entry[0]);
        }, $files);

        // gets all info at once :
        $iter = $this->storage->searchInfoByTitle($title);

        $ret = [];
        foreach ($iter as $entry) {
            if ($entry instanceof WikiFile) {
                $info = [
                    'height' => $entry->getHeight(),
                    'width' => $entry->getWidth(),
                    'url' => $entry->getUrl(),
                    'mediatype' => 'BITMAP',
                    'mime' => $entry->getMimeType(),
                    'badFile' => false
                ];
            } else {
                $info = null;
            }

            $ret[] = $info;
        }

        return $ret;
    }

    /**
     * Returns an array of informations for a given list of pages, by name
     * @param type $pageConfigOrTitle
     * @param array $titles
     * @return array
     */
    #[Override]
    public function getPageInfo($pageConfigOrTitle, array $titles): array
    {
        $iter = $this->repository->searchPkByTitle($titles);
        $ret = [];

        foreach ($iter as $title => $pk) {
            if (!is_null($pk)) {
                $ret[$title] = [
                    'pageId' => $pk,
                    'revId' => 1,
                    'missing' => false,
                    'known' => true,
                    'redirect' => false,
                    'linkclasses' => []
                ];
            } else {
                $ret[$title] = [
                    'pageId' => null,
                    'revId' => null,
                    'missing' => true,
                    'known' => false,
                    'redirect' => false,
                    'linkclasses' => [],
                ];
            }
        }

        return $ret;
    }

    public function logLinterData(PageConfig $pageConfig, array $lints): void {}

    public function parseWikitext(PageConfig $pageConfig, ContentMetadataCollector $metadata, string $wikitext): string
    {
        throw new ParsoidException('Not implemented (should not be called');
    }

    public function preprocessWikitext(PageConfig $pageConfig, ContentMetadataCollector $metadata, string $wikitext): string
    {
        throw new ParsoidException('Not implemented (should not be called');
    }

    public function addTrackingCategory(
            PageConfig $pageConfig,
            ContentMetadataCollector $metadata,
            string $key
    ): void {}
}
