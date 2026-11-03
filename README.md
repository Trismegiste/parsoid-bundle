# Parsoid Bundle for Symfony8 / PHP8

This bundle is a Tesseract Vault for the infamous god-object Wikimedia\Parsoid\Config\Env.
Like a Necron, use the power of Parsoid without getting destroyed and parse wikitext to html without installing
the MediaWiki behemoth from Wikimedia.

Full compatibility with the official wikitext language :
* Use of the official PEG
* Wikitext templates (and wikicode) are supported (unlike the original Parsoid)
* Fully customizable : use the Symfony routing when rendering the wikilinks and pictures
* No need for xxSQL, this bundle is database-agnostic (even if NoSQL is the obvious choice)
* Store your wikitext content, medias and templates how you like, just one contract to implement
* Easy to implement : contracts and design patterns are a universal language
* Extensive use of the DiC from Symfony, it's full of factories fully overridable if you need it
* Native Tag Handlers are fully supported (one tag in the DiC and you're good)
* Two tag handlers provided as sample : a picture carrousel and an infobox-like

Missing features :
* Only 2 namespaces are supported : empty (default) for pages and "file:" for pictures
* Pre-Save-Transform (a.k.a "PST") operations like the "subst:" and "safesubst:"
Those operations are still remaining in the MediaWiki Core
(I'm currently experimenting some black magic to enable this)

# Installation

## Composer
Add the bundle with composer require trismegiste/parsoid-bundle

## Kernel
Add TrismegisteParsoidBundle to config/bundles.php
(could be automagic with Flex)

## Configure providers
You need to configure the 3 providers for accessing to wikitext contents, pictures and templates.
It's under the alias "trismegiste_parsoid". Just follow the errors of bin/console.
Use bin/console conf:dump trismegiste_parsoid for the documentation.

There are 3 contracts to implement for each provider.

## Configure Wikilinks
Obviously you need to override the wikilinks to target your own pages and pictures (for browser, for PDF, for ePub...).
* Extend LinkOverride (it's a Template Method design pattern) by using the Symfony router (and other service if you need it).
* Tag this service with the tag "parsoid.target" and give it a name with "target" property name, for example it could look like :
```yaml
    Trismegiste\GamemasterBundle\Parsoid\Link\BrowserOverride:
        tags:
            - {name: 'parsoid.target', target: 'browser'}
```

# Usage

## Service
For each tagged target (i.e implementations of LinkOverride), a parser will be created.
You can have a parser for inside your app, for generating PDF, for generating ePub...
(it's only an empty shell, the god-object Env is instantiated only when calling the method "parse").

The name of the parser is "parsoid.target.name_of_the_target".
Use bin/console deb:cont for documentation.

## Calling the parser
This service has a method parse() which gets wikitext and returns html.
The code is fully documented for phpDocumentor and fully tested with PhpUnit.

### Note
You have a LinkDefault class as an identity for LinkOverride (it overrides nothing) and keeps the original wikilinks from MediaWiki.

# Extending your content

## Adding Tag Handler
In another bundle or in your app, extend the original contract Wikimedia\Parsoid\Ext\ExtensionTagHandler.
Tag this service with 'parsoid.tag' and set the tag name with the 'tag' property. For example :
```yaml
    Trismegiste\ParsoidBundle\TagHandler\Param:
        tags:
            - {name: 'parsoid.tag', tag: 'param' }
```

and you're done, the tag is now added to the parsing pipeline of Parsoid (for all targets).

# Experimenting with Parsoid
Look at TemplateSubstitutionTransformer and roll a Sanity check