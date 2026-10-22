<?php

/*
 * ParsoidBundle
 */

namespace Trismegiste\ParsoidBundle;

use Trismegiste\ParsoidBundle\Internal\DataAccess;
use Trismegiste\ParsoidBundle\Internal\ParsoidLinkTarget;
use Wikimedia\Parsoid\Config\Env;
use Wikimedia\Parsoid\Config\SiteConfig;
use Wikimedia\Parsoid\DOM\Element;
use Wikimedia\Parsoid\Html2Wt\WikitextSerializer;
use Wikimedia\Parsoid\Tokens\SelfclosingTagTk;
use Wikimedia\Parsoid\Utils\ContentUtils;
use Wikimedia\Parsoid\Utils\DOMCompat;
use Wikimedia\Parsoid\Utils\DOMDataUtils;
use Wikimedia\Parsoid\Utils\DOMUtils;
use Wikimedia\Parsoid\Utils\Title;
use Wikimedia\Parsoid\Wt2Html\Frame;
use Wikimedia\Parsoid\Wt2Html\PegTokenizer;

/**
 * This service aims to emulate the "subst:" for parsoid templates.
 * WARNING: This feature is partially implemented and very experimental.
 * I'm not sure what I am doing.
 * This feature is still in the MediaWiki Core, not in Parsoid
 * because it happens in the PreSaveTransform (aka "PST") stage from the persistence.
 * Even the Parsoid team is struggling to include or not this feature 
 * and APIs are evolving back and forth (look at methods with "PST" in their names).
 * 
 * So I don't pretend to recreate all the specs of this huge software,
 * I will start by simulating a dumb copy-paste of the included template (with crude parameter remplacements),
 * that's the most useful feature for "subst:".
 * 
 * One good thing about this, though:
 * For the search of template substitution tags I'm not using some half-assed regex
 * but the PegTokenizer from Parsoid, the real McCoy.
 * @todo Using the PegTokenizer could be nice for searching outbound links in Vertex
 */
class TemplateSubstitutionTransformer
{
    const parserOpts = [
        'wrapSections' => false,
        'nativeTemplateExpansion' => true,
        'skipLanguageConversionPass' => true
    ];

    protected SiteConfig $siteConfig;
    protected ParserContextFactory $contextFactory;

    public function __construct(SiteConfigFactory $scf, protected DataAccess $dataAccess)
    {
        // we'll be using a generic SiteConfig with a neutral link renderer, we don't care because there's no render at the end
        $this->siteConfig = $scf->create('pst', new LinkDefault());
        $this->contextFactory = new ParserContextFactory($this->siteConfig, $this->dataAccess);
    }

    /**
     * Physically replace all templates prefixed by "subst:" in the given wikitext content
     * @param string $wikitext
     * @return string the new content with templates replaced by their content from the DB
     */
    public function replaceSubst(string $wikitext): string
    {
        $env = $this->buildEnv($wikitext);

        $tokenizer = new PegTokenizer($env);
        $expanded = $tokenizer->process($wikitext, ['sol' => true]);

        foreach ($expanded as $token) {
            if ($token instanceof SelfclosingTagTk && ($token->getName() === 'template')) {
                $key = $token->attribs[0]->k;
                if (is_string($key) && preg_match('#^subst:(.+)$#', $key, $match)) {
                    $stringToReplace = $token->dataParsoid->src;
                    $templateName = trim($match[1]);
                    $transclusion = $this->dataAccess->fetchTemplateSource($env->getPageConfig(), new ParsoidLinkTarget($templateName));
                    if (is_null($transclusion)) {
                        continue;
                    }

                    array_shift($token->attribs);
                    $parsed = $this->processTemplate($transclusion->getContent('main'), $token->attribs);

                    $wikitext = str_replace($stringToReplace, $parsed, $wikitext);
                }
            }
        }

        return $wikitext;
    }

    /**
     * Ful-cycle of template rendering for replacement
     * @param string $template
     * @param array $param
     * @return string
     */
    protected function processTemplate(string $template, array $param): string
    {
        $env = $this->buildEnv($template); // because Env is not re-usable

        $frame = new Frame(Title::newFromText('substition', $env->getSiteConfig()), $env, $param, $template);
        $pipeline = $this->contextFactory->createFullParserPipeline($env, $frame);

        $doc = $pipeline->parseChunkily($template, ['sol' => true])->ownerDocument;
        $body = DOMCompat::getBody($doc);

        // adding data-parsoid attributes
        DOMDataUtils::visitAndStoreDataAttribs($body, [
            'storeInPageBundle' => false,
            'outputContentVersion' => $env->getOutputContentVersion(),
        ]);

        DOMUtils::visitDOM($body, function ($node, array $options = []) {
            if (($node instanceof Element) &&
                (DOMUtils::matchTypeOf($node, '#mw:Param#') || DOMUtils::matchTypeOf($node, '#mw:Transclusion#'))) {
                DOMDataUtils::removeAttributeObject($node, 'typeof');
            }
        }, []);

        $html = ContentUtils::toXML($body, ['innerXML' => true]);

        $serializer = new WikitextSerializer($env, []);
        return $serializer->htmlToWikitext([], $html);
    }

    public function buildEnv(string $wikitext): Env
    {
        $pageContent = $this->contextFactory->createPageContent($wikitext);
        $pageConfig = $this->contextFactory->createPageConfig($pageContent, 'PST', 'en');

        return $this->contextFactory->createEnv($pageConfig, self::parserOpts);
    }
}
