<?php
// Script untuk test login dan akses dashboard
require_once 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

$client = new Client();
$jar = new CookieJar();

// Login
$loginResponse = $client->post('http://127.0.0.1:8000/login', [
    'form_params' => [
        'email' => 'pelanggan@test.com',
        'password' => 'password123',
        '_token' => 'test' // Untuk testing, bisa dikosongkan
    ],
    'cookies' => $jar,
    'allow_redirects' => false
]);

echo "Login Status: " . $loginResponse->getStatusCode() . "\n";

// Akses Dashboard  
$dashboardResponse = $client->get('http://127.0.0.1:8000/dashboard', [
    'cookies' => $jar,
    'allow_redirects' => false
]);

echo "Dashboard Status: " . $dashboardResponse->getStatusCode() . "\n";
echo "Response Headers:\n";
print_r($dashboardResponse->getHeaders());
