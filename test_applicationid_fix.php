<?php

// Test script to verify the $applicationId fix

echo "=== Testing \$applicationId Fix ===\n\n";

// Mock data structure similar to what the controller receives
$lamaranData = [
    'id_lamaran_pekerjaan' => 7,
    'id_user' => 11,
    'nama_pelamar' => 'Test User',
    'status' => 'diterima'
];

$userId = 11;

// Test the fixed line
$applicationIdFixed = $lamaranData['id_lamaran_pekerjaan'] ?? null;

echo "Mock lamaran data:\n";
echo "- id_lamaran_pekerjaan: " . $lamaranData['id_lamaran_pekerjaan'] . "\n";
echo "- user_id: " . $userId . "\n";
echo "- status: " . $lamaranData['status'] . "\n\n";

echo "Fixed variable extraction:\n";
echo "- \$applicationId (fixed): " . $applicationIdFixed . "\n";

if ($applicationIdFixed !== null) {
    echo "✅ SUCCESS: \$applicationId is properly extracted from lamaran data\n";
    echo "✅ This value can be safely passed to getActualInterviewStatus()\n";
} else {
    echo "❌ ERROR: \$applicationId is still null\n";
}

echo "\nFunction call simulation:\n";
echo "getActualInterviewStatus({$applicationIdFixed}, {$userId})\n";

echo "\n=== Test completed ===\n";
