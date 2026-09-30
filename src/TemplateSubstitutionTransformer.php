<?php

/*
 * ParsoidBundle
 */

namespace Trismegiste\ParsoidBundle;

use RuntimeException;
use Trismegiste\ParsoidBundle\Internal\DataAccess;
use Trismegiste\ParsoidBundle\Internal\ParsoidLinkTarget;
use Trismegiste\ParsoidBundle\Internal\ParsoidPageConfig;
use Trismegiste\ParsoidBundle\Internal\ParsoidPageContent;
use Wikimedia\Parsoid\Config\Env;
use Wikimedia\Parsoid\Config\SiteConfig;
use Wikimedia\Parsoid\Config\StubMetadataCollector;
use Wikimedia\Parsoid\Tokens\SelfclosingTagTk;
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
    protected SiteConfig $siteConfig;

    public function __construct(SiteConfigFactory $scf, protected DataAccess $dataAccess)
    {
        // we'll be using a generic SiteConfig with a neutral link renderer, we don't care because there's no render at the end
        $this->siteConfig = $scf->create('pst', new LinkDefault());
    }

    /**
     * Physically replace all templates prefixed by "subst:" in the given wikitext content
     * @param string $wikitext
     * @return string the new content with templates replaced by their content from the DB
     */
    public function replaceSubst(string $wikitext): string
    {
        $env = static::buildEnv($this->siteConfig, $this->dataAccess, $wikitext);

        $tokenizer = new PegTokenizer($env);
        $expanded = $tokenizer->process($wikitext, ['sol' => true]);

        foreach ($expanded as $token) {
            if ($token instanceof SelfclosingTagTk && ($token->getName() === 'template')) {
                $key = $token->attribs[0]->k;
                if (is_string($key) && preg_match('#^subst:(.+)$#', $key, $match)) {
                    $stringToReplace = $token->dataParsoid->src;
                    $templateName = trim($match[1]);

                    $param = [];
                    for ($idx = 1; $idx < count($token->attribs); $idx++) {
                        $kv = $token->attribs[$idx];
                        // named parameter or indexed parameter ?
                        $key = empty($kv->k) ? $idx : $kv->k; // @todo Should I use $idx or a counter in parallel for unnamed parameters ? I don't know.
                        $param[$key] = $kv->v;
                    }

                    try {
                        $parsed = $this->processTemplate($templateName, $param);
                        $wikitext = str_replace($stringToReplace, $parsed, $wikitext);
                    } catch (\Exception $ex) {
                        // don't care, some logs perhaps ?
                    }
                }
            }
        }

        return $wikitext;
    }

    /**
     * Crude replacement of parameters of the template. No recursive replacement.
     * @param string $templateName
     * @param array $param
     * @return string
     */
    protected function processTemplate(string $templateName, array $param): string
    {
        $mock = new ParsoidPageConfig(new ParsoidPageContent(''), $templateName, 'en');
        $content = $this->dataAccess->fetchTemplateSource($mock, new ParsoidLinkTarget($templateName));
        if (is_null($content)) {
            throw new RuntimeException("$templateName is missing");
        }

        return preg_replace_callback('#(\{\{\{([^\}]+)\}\}\})#', function ($match) use ($param) {
            $placeholder = explode('|', $match[2]);

            // if the parameter exists in the given list associated with the template, we replace it
            if (key_exists($placeholder[0], $param)) {
                return $param[$placeholder[0]];
            }

            // the param didn't exist but there is a default value so we replace it
            if (2 === count($placeholder)) {
                return $placeholder[1];
            }

            // No given param, no default, we keep the entry as it was.
            // Another option is to simply throw an exception. No subst until all parameters are filled
            return $match[0];
        }, $content->getContent('main'));
    }

    // In case we need to build Env in another place, it's static public. Usually I hate this but this class is a laboratory.
    // Env cannot be in the DIC because it depends on the wikitext content, meaning we could only implement a Env factory as a service.
    // (Also, I have a feeling Env wasn't created to be reusable for multiple parsings. 1 Env = 1 parsing)
    static public function buildEnv(SiteConfig $siteConfig, DataAccess $dataAccess, string $wikitext): Env
    {
        $pageContent = new ParsoidPageContent($wikitext);
        $pageConfig = new ParsoidPageConfig($pageContent, $wikitext, 'en');
        $metadata = new StubMetadataCollector($siteConfig);

        return new Env($siteConfig, $pageConfig, $dataAccess, $metadata, Parser::parserOpts);
    }
}
