[![World Art](http://www.world-art.ru/img/logo.gif)](http://www.world-art.ru)

[![Latest Stable Version](https://img.shields.io/packagist/v/anime-db/world-art-browser-bundle.svg?maxAge=3600&label=stable)](https://packagist.org/packages/anime-db/world-art-browser-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/anime-db/world-art-browser-bundle.svg?maxAge=3600)](https://packagist.org/packages/anime-db/world-art-browser-bundle)
[![Build Status](https://img.shields.io/travis/anime-db/world-art-browser-bundle.svg?maxAge=3600)](https://travis-ci.org/anime-db/world-art-browser-bundle)
[![Coverage Status](https://img.shields.io/coveralls/anime-db/world-art-browser-bundle.svg?maxAge=3600)](https://coveralls.io/github/anime-db/world-art-browser-bundle?branch=master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/anime-db/world-art-browser-bundle.svg?maxAge=3600)](https://scrutinizer-ci.com/g/anime-db/world-art-browser-bundle/?branch=master)
[![SensioLabs Insight](https://img.shields.io/sensiolabs/i/ecc1f470-e9f7-4972-b503-7ee0d77de3ab.svg?maxAge=3600&label=SLInsight)](https://insight.sensiolabs.com/projects/ecc1f470-e9f7-4972-b503-7ee0d77de3ab)
[![StyleCI](https://styleci.io/repos/43503665/shield?branch=master)](https://styleci.io/repos/43503665)
[![License](https://img.shields.io/packagist/l/anime-db/world-art-browser-bundle.svg?maxAge=3600)](https://github.com/anime-db/world-art-browser-bundle)

World-Art.ru API browser
========================

Installation
------------

Pretty simple with [Composer](http://packagist.org), run:

```sh
composer require anime-db/world-art-browser-bundle
```

Add AnimeDbWorldArtBrowserBundle to your application kernel

```php
// app/appKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new AnimeDb\Bundle\WorldArtBrowserBundle\AnimeDbWorldArtBrowserBundle(),
    );
}
```

Configuration
-------------

```yml
anime_db_world_art_browser:
    # Host name
    # As a default used 'http://www.world-art.ru'
    host: 'http://www.world-art.ru'

    # HTTP User-Agent
    # No default value
    client: 'My Custom Bot 1.0'
```

Usage
-----

First get browser

```php
$browser = $this->get('anime_db.world_art.browser');
```

Get info for anime [Akira](http://www.world-art.ru/animation/animation.php?id=1):

```php
$content = $browser->get('/animation/animation.php?id=1');
```

Catch exceptions

```php
use AnimeDb\Bundle\WorldArtBrowserBundle\Exception\BannedException;
use AnimeDb\Bundle\WorldArtBrowserBundle\Exception\NotFoundException;

try {
    $content = $browser->get('/animation/animation.php?id=1');
} catch (BannedException $e) {
    // you are banned
} catch (NotFoundException $e) {
    // page not found
} catch (\Exception $e) {
    // other exceptions
}
```

You can customize request options. See [Guzzle Documentation](http://docs.guzzlephp.org/en/stable/request-options.html).

License
-------

This bundle is under the [GPL v3 license](http://opensource.org/licenses/GPL-3.0).
See the complete license in the file: LICENSE
