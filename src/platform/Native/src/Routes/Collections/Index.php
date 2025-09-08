<?php

declare(strict_types=1);

use PlatformBridge\RouteCollection;
use NativePlatform\Routes\Controllers\IndexController;

/**
 * Example route configuration
 *
 * RouteCollection::prefix('blog', function ()
 * {
 *     // GET /blog/
 *     RouteCollection::register(
 *         'blog.index',
 *         'GET',
 *         '/',
 *         [BlogController::class, 'index']
 *     );
 *
 *     RouteCollection::register(
 *         'blog.show',
 *         'GET',
 *         '/{slug}',
 *         [BlogController::class, 'pages']
 *     );
 * });
 */

return function ()
{
    RouteCollection::register(
        'app.index',
        ['GET'],
        '/',
        [IndexController::class, 'index']
    );

    RouteCollection::register(
        'app.stream',
        ['GET', 'POST'],
        '/chat',
        [IndexController::class, 'stream']
    );
};
