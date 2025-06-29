<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/dashboard';
    protected $authService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(AuthService $authService)
    {
        $this->middleware('guest');
        $this->authService = $authService;
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'nama' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'string', 'in:admin,hrd,pegawai,pelamar,kasir,front office,dokter,beautician,pelanggan'],
            'alamat' => ['nullable', 'string'],
        ]);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        // Kirim data registrasi ke API
        $response = $this->authService->register([
            'username' => $request->username,
            'email' => $request->email,
            'nama' => $request->nama,
            'password' => $request->password,
            'password_confirmation' => $request->password_confirmation,
            'role' => $request->role ?? 'pelamar',
            'phone' => $request->phone,
            'alamat' => $request->alamat,
        ]);

        if ($response['status'] === 'success') {
            // Registrasi berhasil, ambil data user dari API
            $apiUser = $response['data']['user'];
            
            // Login otomatis setelah registrasi
            $user = new \App\Models\User();
            $user->id = $apiUser['id'];
            $user->email = $apiUser['email'];
            $user->name = $apiUser['nama'];
            $user->role = $apiUser['role'];
            $user->username = $apiUser['username'];
            $user->is_active = $apiUser['status'] === 'aktif';
            
            Auth::login($user);
            
            return redirect($this->redirectPath());
        }

        // Jika registrasi gagal
        return back()
            ->withInput($request->all())
            ->withErrors(['email' => [__('Registrasi gagal. Silakan coba lagi.')]]);
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }
}
