<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test authentication
echo "Testing Laravel Authentication and Role Middleware\n";
echo "================================================\n\n";

// Test 1: Check if HRD user exists
$hrdUser = App\Models\User::where('role', 'hrd')->first();
if ($hrdUser) {
    echo "✓ HRD User found: {$hrdUser->name} ({$hrdUser->email})\n";
} else {
    echo "✗ HRD User not found\n";
}

// Test 2: Check if Admin user exists
$adminUser = App\Models\User::where('role', 'admin')->first();
if ($adminUser) {
    echo "✓ Admin User found: {$adminUser->name} ({$adminUser->email})\n";
} else {
    echo "✗ Admin User not found\n";
}

// Test 3: Check role methods
if ($hrdUser) {
    echo "✓ HRD isHRD(): " . ($hrdUser->isHRD() ? 'true' : 'false') . "\n";
    echo "✓ HRD isAdmin(): " . ($hrdUser->isAdmin() ? 'true' : 'false') . "\n";
}

if ($adminUser) {
    echo "✓ Admin isHRD(): " . ($adminUser->isHRD() ? 'true' : 'false') . "\n";
    echo "✓ Admin isAdmin(): " . ($adminUser->isAdmin() ? 'true' : 'false') . "\n";
}

// Test 4: Check role middleware logic
echo "\nTesting Role Middleware Logic:\n";
$allowedRoles = ['admin', 'hrd'];

if ($hrdUser) {
    $hrdHasAccess = in_array($hrdUser->role, $allowedRoles);
    echo "✓ HRD role '{$hrdUser->role}' in allowed roles: " . ($hrdHasAccess ? 'true' : 'false') . "\n";
}

if ($adminUser) {
    $adminHasAccess = in_array($adminUser->role, $allowedRoles);
    echo "✓ Admin role '{$adminUser->role}' in allowed roles: " . ($adminHasAccess ? 'true' : 'false') . "\n";
}

// Test 5: Check if middleware is registered
echo "\nChecking middleware registration:\n";
$app = app();
$router = $app['router'];
$middlewares = $app['router']->getMiddleware();

if (isset($middlewares['role'])) {
    echo "✓ Role middleware is registered\n";
    echo "  Class: " . $middlewares['role'] . "\n";
} else {
    echo "✗ Role middleware is NOT registered\n";
}

echo "\nTest completed.\n";
