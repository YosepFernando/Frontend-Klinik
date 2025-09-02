<?php
// File: middleware_debug.php
// Checking middleware registration

require_once __DIR__ . '/vendor/autoload.php';

echo "Checking middleware registration...\n\n";

// Bootstrap Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->bootstrap();

// Get router instance
$router = app('router');

// Check middleware aliases
$aliases = $router->getMiddleware();
echo "Middleware Aliases:\n";
print_r($aliases);

// Check route middlewareGroups
$groups = $router->getMiddlewareGroups();
echo "\nMiddleware Groups:\n";
print_r($groups);

// Check if our middleware classes exist
echo "\nMiddleware Class Check:\n";
echo "EnsureApiSessionAuth exists: " . (class_exists('App\Http\Middleware\EnsureApiSessionAuth') ? "YES" : "NO") . "\n";
echo "ApiAuthMiddleware exists: " . (class_exists('App\Http\Middleware\ApiAuthMiddleware') ? "YES" : "NO") . "\n";

// Check if specific routes use our middleware
echo "\nRoute Middleware Check:\n";
$routes = $router->getRoutes();
foreach ($routes as $route) {
    if (strpos($route->uri, 'dashboard') !== false) {
        echo "Route: " . $route->uri . "\n";
        echo "  Methods: " . implode(', ', $route->methods) . "\n";
        echo "  Middleware: " . implode(', ', $route->action['middleware'] ?? []) . "\n";
        echo "  Controller: " . ($route->action['controller'] ?? 'None') . "\n";
        echo "\n";
    }
}
