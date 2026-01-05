<?php

use Engine\Router;

/**
 * --------------------------------------------------------------------------
 * Manual Routes
 * --------------------------------------------------------------------------
 * Define your custom routes here.
 * 
 * Magic Routing is ENABLED:
 * If a route is not defined here, framework will look for:
 * /name -> NameController::index()
 * /name/action -> NameController::action()
 */

Router::get('/', 'WelcomeController@index');


// Default Routes
Router::get('/about', 'HomeController@about');

// Examples (Manual):
// Router::get('/login', 'AuthController@loginForm');





// Magic Routes (Automatic):
// /dashboard       -> DashboardController::index()
// /auth/login      -> AuthController::login()