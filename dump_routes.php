<?php

// Simpan file ini di root aplikasi

// Nonaktifkan output buffering
if (ob_get_level()) ob_end_clean();

// Set header sebagai plaintext agar mudah dibaca
header('Content-Type: text/plain');

echo "=== Routes Dump ===\n\n";

// Bootstrap Laravel application
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get all registered routes
$routes = app(\Illuminate\Routing\Router::class)->getRoutes();

// Sort routes by URI
$sortedRoutes = [];
foreach ($routes as $route) {
    $uri = $route->uri();
    $method = implode('|', $route->methods());
    $name = $route->getName();
    $action = $route->getActionName();
    
    // Ambil middleware
    $middleware = $route->middleware();
    $middlewareStr = implode(', ', $middleware);
    
    // Format untuk mencetak
    $sortedRoutes[] = [
        'uri' => $uri,
        'method' => $method,
        'name' => $name ?: '(unnamed)',
        'action' => $action,
        'middleware' => $middlewareStr
    ];
}

// Sort by URI
usort($sortedRoutes, function($a, $b) {
    return strcmp($a['uri'], $b['uri']);
});

// Print routes in a readable format
foreach ($sortedRoutes as $route) {
    echo "Route: " . $route['method'] . " " . $route['uri'] . "\n";
    echo "  Name: " . $route['name'] . "\n";
    echo "  Action: " . $route['action'] . "\n";
    echo "  Middleware: " . $route['middleware'] . "\n";
    echo "\n";
}

echo "Total routes: " . count($sortedRoutes) . "\n";
echo "\n=== Authentication Routes ===\n\n";

// Specifically filter authentication routes
$authRoutes = array_filter($sortedRoutes, function($route) {
    return str_contains($route['uri'], 'login') || 
           str_contains($route['uri'], 'logout') ||
           str_contains($route['uri'], 'register') ||
           str_contains($route['uri'], 'password') ||
           str_contains($route['uri'], 'auth');
});

foreach ($authRoutes as $route) {
    echo "Route: " . $route['method'] . " " . $route['uri'] . "\n";
    echo "  Name: " . $route['name'] . "\n";
    echo "  Action: " . $route['action'] . "\n";
    echo "  Middleware: " . $route['middleware'] . "\n";
    echo "\n";
}

echo "=== Protected Routes ===\n\n";

// Filter protected routes (those with auth middleware)
$protectedRoutes = array_filter($sortedRoutes, function($route) {
    return str_contains($route['middleware'], 'api.auth') || 
           str_contains($route['middleware'], 'auth');
});

foreach ($protectedRoutes as $route) {
    echo "Route: " . $route['method'] . " " . $route['uri'] . "\n";
    echo "  Name: " . $route['name'] . "\n";
    echo "  Action: " . $route['action'] . "\n";
    echo "  Middleware: " . $route['middleware'] . "\n";
    echo "\n";
}

echo "Total protected routes: " . count($protectedRoutes) . "\n";
echo "\n=== Test Complete ===\n";
