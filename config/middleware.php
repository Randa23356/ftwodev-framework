<?php

return [
    /**
     * Global Middlewares
     * These run on every request.
     */
    'global' => [
        // \Projects\Middlewares\ExampleMiddleware::class,
    ],

    /**
     * Named Middlewares
     * Can be assigned to specific routes.
     */
    'named' => [
        'auth' => \Projects\Middlewares\AuthMiddleware::class,
    ],
];
