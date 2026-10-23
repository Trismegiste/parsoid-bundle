# Parsoid Bundle for Symfony8 / PHP8

This bundle is a Tesseract Vault for the infamous god-object Wikimedia\Parsoid\Config\Env.
Like a Necron, use the power of Parsoid without getting destroyed.

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
* Extend LinkOverride (it's a Template Method design pattern) by using the Symfony router (and other service if you need it).
* Tag this service with the tag "parsoid.target" and give it a name with "target" property name, for example it could look like :
```yaml
    Trismegiste\GamemasterBundle\Parsoid\Link\BrowserOverride:
        tags:
            - {name: 'parsoid.target', target: 'browser'}
```

# Usage

## Service
For each target, a parser will be created,
you can specialize your html links for each targeted rendering (for browser, for PDF, for ePub...).
The name of the service is "parsoid.target.<name of the target>".
Use bin/console deb:cont for documentation.

## Calling the parser
This service has a method parse() which gets wikitext and returns html.