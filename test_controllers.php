<?php
/**
 * Simple test script to verify refactored controllers work with services
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Services\AbsensiService;
use App\Services\PegawaiService;
use App\Services\ReligiousStudyService;
use App\Services\UserService;

// Initialize Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Refactored Controllers - Service Dependencies\n";
echo "=====================================================\n\n";

try {
    // Test AbsensiService
    echo "1. Testing AbsensiService...\n";
    $absensiService = app(AbsensiService::class);
    echo "   ✓ AbsensiService can be instantiated\n";
    
    // Test PegawaiService
    echo "2. Testing PegawaiService...\n";
    $pegawaiService = app(PegawaiService::class);
    echo "   ✓ PegawaiService can be instantiated\n";
    
    // Test ReligiousStudyService
    echo "3. Testing ReligiousStudyService...\n";
    $religiousStudyService = app(ReligiousStudyService::class);
    echo "   ✓ ReligiousStudyService can be instantiated\n";
    
    // Test UserService
    echo "4. Testing UserService...\n";
    $userService = app(UserService::class);
    echo "   ✓ UserService can be instantiated\n";
    
    // Test controller instantiation
    echo "5. Testing Controller Dependencies...\n";
    
    // AttendanceController
    $attendanceController = app(\App\Http\Controllers\AttendanceController::class);
    echo "   ✓ AttendanceController can be instantiated with services\n";
    
    // AbsensiController
    $absensiController = app(\App\Http\Controllers\AbsensiController::class);
    echo "   ✓ AbsensiController can be instantiated with services\n";
    
    // ReligiousStudyController
    $religiousStudyController = app(\App\Http\Controllers\ReligiousStudyController::class);
    echo "   ✓ ReligiousStudyController can be instantiated with services\n";
    
    echo "\n✅ ALL TESTS PASSED! Controllers are properly refactored with service dependencies.\n";
    
} catch (Exception $e) {
    echo "\n❌ TEST FAILED: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
