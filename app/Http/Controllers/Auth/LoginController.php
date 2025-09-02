<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    protected $authService;
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(AuthService $authService)
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('api.auth')->only('logout');
        $this->authService = $authService;
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // Coba login menggunakan API
        $response = $this->authService->login($request->email, $request->password);
        
        // Log response untuk debugging
        \Log::info('Login API Response:', [
            'response' => $response,
            'email' => $request->email
        ]);

        // Periksa apakah response valid dan berhasil
        if (is_array($response) && 
            isset($response['status']) && 
            $response['status'] === 'success') {
            
            // Check if user data exists in different possible structures
            $apiUser = null;
            if (isset($response['data']['user'])) {
                $apiUser = $response['data']['user'];
            } elseif (isset($response['user'])) {
                $apiUser = $response['user'];
            } elseif (isset($response['data']) && is_array($response['data']) && !isset($response['data']['user'])) {
                // Sometimes the user data is directly in 'data'
                $apiUser = $response['data'];
            }
            
            if ($apiUser) {
                // Login berhasil, simpan data user dari API
                $user = $this->createUserFromApiResponse($apiUser);
                
                // Simpan token ke session untuk digunakan di API calls
                $token = $response['data']['token'] ?? null;
                if ($token) {
                    Session::put('api_token', $token);
                    \Log::info('API Token saved to session', ['token' => substr($token, 0, 20) . '...']);
                }
                
                // Simpan data user lengkap ke session
                Session::put('api_user', $apiUser);
                Session::put('user_role', $user->role);
                Session::put('authenticated', true);
                Session::put('user_id', $user->id);
                Session::put('user_email', $user->email);
                Session::put('user_name', $user->name);
                
                // Coba dapatkan data pegawai jika user bukan admin
                if (!in_array(strtolower($user->role), ['admin', 'hrd'])) {
                    try {
                        // Buat instance PegawaiService untuk mendapatkan data pegawai
                        $pegawaiService = app(\App\Services\PegawaiService::class);
                        
                        \Log::info('LoginController - Attempting to get pegawai data for user:', [
                            'user_id' => $user->id,
                            'user_role' => $user->role,
                            'token_available' => !empty($token)
                        ]);
                        
                        // Set token untuk API call
                        if ($token) {
                            $pegawaiService->withToken($token);
                        }
                        
                        // **Cara baru: Gunakan endpoint khusus untuk mendapatkan data pegawai sendiri**
                        $myPegawaiResponse = $pegawaiService->getMyPegawaiData();
                        
                        \Log::info('LoginController - My Pegawai API response:', [
                            'user_id' => $user->id,
                            'response_status' => $myPegawaiResponse['status'] ?? 'N/A',
                            'response_message' => $myPegawaiResponse['message'] ?? $myPegawaiResponse['pesan'] ?? 'N/A',
                            'has_data' => isset($myPegawaiResponse['data']),
                            'data_type' => isset($myPegawaiResponse['data']) ? gettype($myPegawaiResponse['data']) : 'N/A'
                        ]);
                        
                        if (isset($myPegawaiResponse['status']) && 
                            in_array($myPegawaiResponse['status'], ['success', 'sukses']) && 
                            !empty($myPegawaiResponse['data'])) {
                            
                            $pegawaiData = $myPegawaiResponse['data'];
                            $pegawaiId = $pegawaiData['id_pegawai'] ?? $pegawaiData['id'] ?? null;
                            
                            if ($pegawaiId) {
                                Session::put('pegawai_data', $pegawaiData);
                                Session::put('pegawai_id', $pegawaiId);
                                
                                \Log::info('LoginController - Found pegawai data and saved to session:', [
                                    'user_id' => $user->id,
                                    'pegawai_id' => $pegawaiId,
                                    'nama_lengkap' => $pegawaiData['nama_lengkap'] ?? $pegawaiData['nama'] ?? 'N/A',
                                    'posisi' => isset($pegawaiData['posisi']['nama_posisi']) ? $pegawaiData['posisi']['nama_posisi'] : 'N/A'
                                ]);
                            }
                        } else {
                            \Log::warning('LoginController - Failed to get my pegawai data:', [
                                'user_id' => $user->id,
                                'response' => $myPegawaiResponse
                            ]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('LoginController - Failed to load pegawai data during login:', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                }
                
                // Set Laravel auth session manually (tanpa menyimpan ke database)
                // Menggunakan pendekatan session-only tanpa model Eloquent
                Session::put('login_web_' . sha1('App\Http\Controllers\Auth\LoginController'), $user->id);
                
                \Log::info('User logged in successfully', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                    'redirect_to' => $this->getRedirectPathByRole($user->role)
                ]);
                
                // Redirect ke halaman yang sesuai berdasarkan role
                $redirectPath = $this->getRedirectPathByRole($user->role);
                
                \Log::info('Melakukan redirect setelah login', [
                    'user_id' => $user->id,
                    'role' => $user->role,
                    'redirect_to' => $redirectPath,
                    'session_data' => [
                        'api_token' => Session::has('api_token') ? 'ada' : 'tidak ada',
                        'authenticated' => Session::get('authenticated'),
                        'user_role' => Session::get('user_role'),
                    ]
                ]);
                
                return redirect($redirectPath);
            } else {
                \Log::error('Login success but no user data found in response', ['response' => $response]);
                $errorMessage = 'Data user tidak ditemukan dalam response API.';
            }
        } else {
            // Response tidak success atau tidak valid
            $errorMessage = 'Kredensial tidak valid atau akun tidak aktif.';
            
            // Jika ada pesan error spesifik dari API, gunakan itu
            if (is_array($response) && isset($response['message'])) {
                $errorMessage = $response['message'];
            } elseif (!is_array($response)) {
                $errorMessage = 'Terjadi kesalahan pada server. Silakan coba lagi.';
                \Log::error('Invalid API response format', ['response' => $response]);
            }
        }

        return back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors(['email' => [__($errorMessage)]]);
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);
    }

    /**
     * Get the post login redirect path.
     *
     * @return string
     */
    protected function redirectPath()
    {
        return $this->redirectTo ?? '/dashboard';
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            // Logout dari API jika ada token
            if (Session::has('api_token')) {
                $logoutResponse = $this->authService->logout();
                \Log::info('API Logout Response', ['response' => $logoutResponse]);
            }
        } catch (\Exception $e) {
            \Log::error('API Logout Error: ' . $e->getMessage());
        }
        
        // Clear all session data
        $request->session()->flush();
        
        // Regenerate session
        $request->session()->regenerate();
        
        \Log::info('User logged out successfully');
        
        return redirect('/login')->with('status', 'Anda telah berhasil logout');
    }
    
    /**
     * Handle API response and create user object
     */
    private function createUserFromApiResponse($apiUser)
    {
        // Create a simple object instead of Eloquent model to avoid database operations
        $user = new \stdClass();
        
        // Map API response fields to user object
        $user->id = $this->getApiField($apiUser, ['id_user', 'id', 'user_id'], 1);
        $user->email = $this->getApiField($apiUser, ['email'], '');
        $user->name = $this->getApiField($apiUser, ['nama_user', 'nama', 'name', 'full_name'], 'User');
        $user->role = $this->getApiField($apiUser, ['role', 'user_role'], 'pegawai');
        $user->username = $this->getApiField($apiUser, ['username', 'email'], '');
        
        // Handle status field
        $status = $this->getApiField($apiUser, ['status', 'is_active'], true);
        $user->is_active = is_bool($status) ? $status : in_array(strtolower($status), ['aktif', 'active', '1', 1]);
        
        // Optional fields from API
        $user->phone = $this->getApiField($apiUser, ['no_telp', 'phone', 'telephone'], null);
        $user->address = $this->getApiField($apiUser, ['address', 'alamat'], null);
        $user->tanggal_lahir = $this->getApiField($apiUser, ['tanggal_lahir', 'birth_date'], null);
        
        return $user;
    }
    
    /**
     * Get field value from API response with fallback options
     */
    private function getApiField($data, $fieldNames, $default = null)
    {
        if (!is_array($data)) {
            return $default;
        }
        
        foreach ($fieldNames as $fieldName) {
            if (isset($data[$fieldName]) && $data[$fieldName] !== null && $data[$fieldName] !== '') {
                return $data[$fieldName];
            }
        }
        
        return $default;
    }
    
    /**
     * Get redirect path based on user role
     */
    private function getRedirectPathByRole($role)
    {
        switch (strtolower($role)) {
            case 'admin':
            case 'hrd':
                return '/dashboard'; // Admin dashboard
                
            case 'dokter':
            case 'beautician':
                return '/dashboard'; // Staff dashboard
                
            case 'front_office':
            case 'kasir':
                return '/dashboard'; // Front office dashboard
                
            case 'pelanggan':
                return '/dashboard'; // Customer dashboard
                
            default:
                return '/dashboard'; // Default dashboard
        }
    }
}
