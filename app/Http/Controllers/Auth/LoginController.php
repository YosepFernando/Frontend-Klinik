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
        $this->middleware('auth')->only('logout');
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

        // Periksa apakah response valid dan berhasil
        if (is_array($response) && 
            isset($response['status']) && 
            $response['status'] === 'success' &&
            isset($response['data']['user'])) {
            
            // Login berhasil, simpan data user dari API
            $apiUser = $response['data']['user'];
            
            // Buat object user untuk session Laravel
            $user = new \App\Models\User();
            $user->id = $apiUser['id'];
            $user->email = $apiUser['email'];
            $user->name = $apiUser['nama'] ?? $apiUser['name'] ?? $apiUser['email']; // Fallback untuk nama
            $user->role = $apiUser['role'] ?? 'pegawai';
            $user->username = $apiUser['username'] ?? $apiUser['email'];
            $user->is_active = isset($apiUser['status']) ? ($apiUser['status'] === 'aktif') : true;
            
            // Login user di Laravel (tanpa mengecek password karena sudah divalidasi oleh API)
            Auth::login($user);
            
            // Redirect ke halaman yang sesuai
            return redirect()->intended($this->redirectPath());
        }

        // Jika login gagal atau response tidak valid, tampilkan pesan error
        $errorMessage = 'Kredensial tidak valid atau akun tidak aktif.';
        
        // Jika ada pesan error spesifik dari API, gunakan itu
        if (is_array($response) && isset($response['message'])) {
            $errorMessage = $response['message'];
        } elseif (!is_array($response)) {
            $errorMessage = 'Terjadi kesalahan pada server. Silakan coba lagi.';
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
        return $this->redirectTo;
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Logout dari API
        $this->authService->logout();
        
        // Logout dari Laravel
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
