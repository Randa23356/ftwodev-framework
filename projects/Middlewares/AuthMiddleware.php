<?php

namespace Projects\Middlewares;

use Engine\MiddlewareInterface;
use CoreModules\AuthModule\Auth;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(callable $next)
    {
        if (!Auth::check()) {
            redirect('/login');
            return;
        }

        return $next();
    }
}
