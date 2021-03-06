<?php

/*
 +------------------------------------------------------------------------+
 | Kitsune                                                                |
 +------------------------------------------------------------------------+
 | Copyright (c) 2013-2015 Phalcon Team and contributors                  |
 +------------------------------------------------------------------------+
 | This source file is subject to the New BSD License that is bundled     |
 | with this package in the file docs/LICENSE.txt.                        |
 |                                                                        |
 | If you did not receive a copy of the license and are unable to         |
 | obtain it through the world-wide-web, please send an email             |
 | to license@phalconphp.com so we can send you a copy immediately.       |
 +------------------------------------------------------------------------+
*/

use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Url as UrlProvider;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Events\Manager as EventsManager;

use Ciconia\Ciconia;
use Kitsune\PostFinder;
use Kitsune\Plugins\NotFoundPlugin;
use Ciconia\Extension\Gfm\FencedCodeBlockExtension;

/**
 * We register the events manager
 */
$di->set(
    'dispatcher',
    function () use ($di) {

        $eventsManager = new EventsManager;

        /**
         * Handle exceptions and not-found exceptions using NotFoundPlugin
         */
        $eventsManager->attach('dispatch:beforeException', new NotFoundPlugin);

        $dispatcher = new Dispatcher;
        $dispatcher->setEventsManager($eventsManager);

        $dispatcher->setDefaultNamespace('Kitsune\Controllers');

        return $dispatcher;
    }
);

/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->set(
    'url',
    function () use ($config) {
        $url = new UrlProvider();
        $url->setBaseUri($config->baseUri);
        return $url;
    }
);

$di->set(
    'view',
    function () use ($config) {

        $view = new View();

        $view->setViewsDir(K_PATH . '/app/views/');

        $view->registerEngines([".volt" => 'volt']);

        //Create an events manager
        //$eventsManager = new EventsManager();

        //Attach a listener for type "view"
        //$eventsManager->attach("view", function($event, $view) {
        //    file_put_contents('a.txt', $event->getType() . ' - ' . $view->getActiveRenderPath() . PHP_EOL, FILE_APPEND);
        //});

        //Bind the eventsManager to the view component
        //$view->setEventsManager($eventsManager);

        return $view;
    }
);

/**
 * Setting up volt
 */
$di->set(
    'volt',
    function ($view, $di) {

        $volt = new VoltEngine($view, $di);

        $volt->setOptions(
            [
                "compiledPath" => K_PATH . '/var/cache/volt/',
                'stat'              => true,
                'compileAlways'     => true,
            ]
        );

        $volt->getCompiler()->addFunction(
            'markdown',
            function ($parameters) {
                return "\$this->markdown->render({$parameters})";
            }
        );

        return $volt;
    },
    true
);

/**
 * Start the session the first time some component request the session service
 */
$di->set(
    'session',
    function () {
        $session = new SessionAdapter();
        $session->start();
        return $session;
    }
);

/**
 * Cache
 */
$di->set(
    'viewCache',
    function () {
        $session = new SessionAdapter();
        $session->start();
        return $session;
    }
);

/**
 * viewCache
 */
$di->set(
    'cache',
    function () use ($config) {
        $frontConfig = $config->cache_data->front->toArray();
        $backConfig  = $config->cache_data->back->toArray();
        $class       = '\Phalcon\Cache\Frontend\\' . $frontConfig['adapter'];
        $frontCache  = new $class($frontConfig['params']);
        /**
         * Backend cache uses our own component which extends Libmemcached
         */
        $class       = '\Phalcon\Cache\Backend\\' . $backConfig['adapter'];
        $cache       = new $class($frontCache, $backConfig['params']);
        return $cache;
    },
    true
);

$di->set(
    'viewCache',
    function () use ($config) {
        $frontConfig = $config->cache_view->front->toArray();
        $backConfig  = $config->cache_view->back->toArray();
        $class       = '\Phalcon\Cache\Frontend\\' . $frontConfig['adapter'];
        $frontCache  = new $class($frontConfig['params']);
        /**
         * Backend cache uses our own component which extends Libmemcached
         */
        $class       = '\Phalcon\Cache\Backend\\' . $backConfig['adapter'];
        $cache       = new $class($frontCache, $backConfig['params']);
        return $cache;
    }
);

/**
 * Markdown renderer
 */
$di->set(
    'markdown',
    function () {
        $ciconia = new Ciconia();

        $ciconia->addExtension(new \Kitsune\Markdown\TableExtension());
        $ciconia->addExtension(new \Kitsune\Markdown\UrlAutoLinkExtension());
        $ciconia->addExtension(new \Ciconia\Extension\Gfm\FencedCodeBlockExtension());
        $ciconia->addExtension(new FencedCodeBlockExtension());
        return $ciconia;
    },
    true
);

$cache = $di->get('cache');
$di->set(
    'finder',
    function () use ($cache) {
        $key = 'post.finder.cache';
        $postFinder = $cache->get($key);
        if (null === $postFinder) {
            $postFinder = new PostFinder();
            $cache->save($key, $postFinder);
        }
        return $postFinder;
    },
    true
);

/**
 * Routes
 */
$router = new Router(false);
$routes = $config->routes->toArray();
foreach ($routes as $pattern => $options) {
    $router->add($pattern, $options);
}

$di->set('router', $router);
