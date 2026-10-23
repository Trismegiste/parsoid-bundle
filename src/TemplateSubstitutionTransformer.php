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
 * WARNING: This feature is highly experimental because I'm not sure what I am doing.
 * Currently, template substitution feature is still in the MediaWiki Core, not in Parsoid library
 * because it happens in the PreSaveTransform (aka "PST") stage from the persistence.
 *
 * Even the Parsoid team is struggling to include or not this feature and which strategy to use (full-cycle or not)
 * and APIs are evolving back and forth (look at methods with "PST" in their names).
 *
 * So I don't pretend to recreate all the specs of this huge software,
 * I will start this emulation with a dumb copy-paste of the subst'ed templates
 * cleaned by a full cycle (wikitext->html->wikitext), therefore,
 * wikicode is executed and parameters are replaced.
 * 
 * This ugly code took me about 3 days to build after many many many tries (and curses) and
 * with the (non-)help of misguided AIs (openai and gemini) confusing releases
 * of Parsoid (including the old js version), mentioning classes and methods from MediaWiki Core
 * that weren't included in the standalone parser and asserting things about Parsoid completely false
 * because Parsoid comments are a mess (there are still JS comments, for the record).
 * 
 * I've even considered to throw away Parsoid for CommonMark (which is nice but limited because no PEG nor LL(k)).
 */
class TemplateSubstitutionTransformer
{
    protected SiteConfig $siteConfig;
    protected ParserContextFactory $contextFactory;

    public function __construct(SiteConfigFactory $scf, protected DataAccess $dataAccess)
    {
        // we'll be using a generic SiteConfig with a neutral link renderer, we don't care because there's no render at the end
        // Besides, overwriting the wikilinks confused the serializer who cannot revert wikilinks as wikitext between brackets.
        $this->siteConfig = $scf->create('pst', new LinkDefault());
        $this->contextFactory = new ParserContextFactory($this->siteConfig, $this->dataAccess);
    }

    /**
     * Physically replace all templates prefixed by "subst:" in the given wikitext content.
     * 
     * For the search of template substitution tags I'm not using some half-assed regex
     * but the PegTokenizer from Parsoid, the real McCoy.
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
                $first = array_shift($token->attribs); // the first item in the attrib array is the template name (recursions end here)
                // we don't care to mess up with the token, it won't be used later
                if (is_string($first->k) && preg_match('#^subst:(.+)$#', $first->k, $match)) {
                    $templateName = trim($match[1]);
                    $transclusion = $this->dataAccess->fetchTemplateSource($env->getPageConfig(), new ParsoidLinkTarget($templateName));
                    if (is_null($transclusion)) {
                        continue;
                    }

                    $parsed = $this->processTemplate($transclusion->getContent('main'), $token->attribs);
                    $wikitext = str_replace($token->dataParsoid->src, $parsed, $wikitext); // could fail if two exact subst are in the page but I can't rely on dataParsoid->tsr
                }
            }
        }

        return $wikitext;
    }

    /**
     * Full-cycle of template rendering for replacement
     * @param string $template
     * @param array $param
     * @return string
     */
    protected function processTemplate(string $template, array $param): string
    {
        $env = $this->buildEnv($template); // because Env is not re-usable

        $frame = new Frame(Title::newFromText('substitution', $env->getSiteConfig()), $env, $param, $template);
        $pipeline = $this->contextFactory->createFullParserPipeline($env, $frame);

        $doc = $pipeline->parseChunkily($template, ['sol' => true])->ownerDocument;
        $body = DOMCompat::getBody($doc);

        // adding data-parsoid attributes
        DOMDataUtils::visitAndStoreDataAttribs($body, [
            'storeInPageBundle' => false,
            'outputContentVersion' => $env->getOutputContentVersion(),
        ]);

        // I don't want to revert parameters and wikicode so I remove typeof attributes :
        DOMUtils::visitDOM($body, function ($node, array $options = []) {
            if (($node instanceof Element) && DOMUtils::matchTypeOf($node, '#mw:(Param|Transclusion)#')) {
                DOMDataUtils::removeAttributeObject($node, 'typeof');
            }
        }, []);

        // to html content...
        $html = ContentUtils::toXML($body, ['innerXML' => true]);
        // ... and back to wikitext
        $serializer = new WikitextSerializer($env, []);

        return $serializer->htmlToWikitext([], $html);
    }

    public function buildEnv(string $wikitext): Env
    {
        $pageContent = $this->contextFactory->createPageContent($wikitext);
        $pageConfig = $this->contextFactory->createPageConfig($pageContent, 'PST', 'en');

        return $this->contextFactory->createEnv($pageConfig, Parser::envOptions);
    }
}
