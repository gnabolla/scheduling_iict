<?php
// router.php

require_once "core/Auth.php";
require_once "core/Middleware.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$scriptName = $_SERVER['SCRIPT_NAME'];
$basePath = rtrim(dirname($scriptName), '/\\');

$uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

if ($uri === '' || $uri === false) {
    $uri = '/';
}

// Define protected routes that require authentication
$protected_routes = [
    "/" => "controllers/index.php",
    "/files" => "files_template.php",
    "/allfiles" => "files.php"
];

// Define public routes
$public_routes = [
    "/login" => "controllers/login.php",
    "/signup" => "controllers/signup.php",
    "/logout" => "controllers/logout.php"
];

// Combine all routes
$routes = array_merge($protected_routes, $public_routes);

define('BASE_PATH', $basePath);

function abort($code = 404) {
    http_response_code($code);
    $title = "{$code} Error";
    require "views/{$code}.php";
    exit();
}

// Updated routing logic with middleware
function routeToController($uri, $routes, $protected_routes) {
    if (array_key_exists($uri, $routes)) {
        // Check if route requires authentication
        if (array_key_exists($uri, $protected_routes)) {
            \Core\Middleware::requireAuth();
        }
        
        $controller = $routes[$uri];
        if (file_exists($controller)) {
            require $controller;
            return;
        }
    }
    abort(404);
}

routeToController($uri, $routes, $protected_routes);