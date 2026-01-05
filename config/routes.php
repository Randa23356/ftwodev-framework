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

Router::get('/', function() {
    return view('welcome');
});

// Examples (Manual):
// Router::get('/login', 'AuthController@loginForm');



// Auth Routes (Added by Bloom)
Router::get('/login', 'AuthController@showLogin');
Router::post('/login', 'AuthController@login');
Router::get('/register', 'AuthController@showRegister');
Router::post('/register', 'AuthController@register');
Router::get('/logout', 'AuthController@logout');
Router::get('/dashboard', 'DashboardController@index');

// Magic Routes (Automatic):
// /dashboard       -> DashboardController::index()
// /auth/login      -> AuthController::login()