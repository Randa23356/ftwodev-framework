<?php

namespace Engine;

interface MiddlewareInterface
{
    /**
     * Handle an incoming request.
     *
     * @param callable $next The next middleware or the controller action.
     * @return mixed
     */
    public function handle(callable $next);
}
