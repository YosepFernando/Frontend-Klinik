@extends('layouts.app')

@section('title', 'Daftar Akun')

@section('hideSidebar')
@endsection

@push('styles')
<style>
    .text-auth {
        font-family: 'Poppins', sans-serif;
        font-weight: 900;
        color: white;
        /* font-family: 'Montserrat', sans-serif; */
    }

    .auth-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 0;
        width: 100%;
    }

        .background-fade{
        background-image: url('{{ asset("images/bg.png") }}');
        background-repeat: no-repeat;
        background-attachment: fixed;
        background-size: cover;
        background-position: center;
        filter: brightness(60%);
        position: fixed;
        width: 100%;
        height: 100%;
        z-index: -100;
    }

    .background {
        background-image: url('{{ asset("images/bg.png") }}');
        background-size: cover;
        background-position: center;
        overflow: hidden;
    }

    .dp-grid {
        display: grid;
    }
    
    .auth-card {
        background: white;
        border-radius: 1.5rem;
        border: none;
        /* max-width: 600px; */
        width: 75%;
        margin: 1rem;
    }
    
    .auth-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 1.5rem 1.5rem 0 0;
        padding: 2rem;
        text-align: center;
        border: none;
    }
    
    .auth-header h4 {
        margin: 0;
        font-weight: 600;
        font-size: 1.5rem;
    }
    
    .auth-body {
        padding: 2rem;
        border-radius: 1.5rem;
    }
    
    .form-floating {
        margin-bottom: 1.5rem;
    }
    
    .form-floating .form-control,
    .form-floating .form-select {
        border-radius: 0.75rem;
        border: 2px solid #e9ecef;
        padding: 1rem 0.75rem;
        height: calc(3.5rem + 2px);
        transition: all 0.3s ease;
    }
    
    .form-floating .form-control:focus,
    .form-floating .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .form-floating label {
        color: #6c757d;
        font-weight: 500;
    }
    
    .btn-register {
        background: linear-gradient(135deg, #8a11b6 0%, #9d32b6 100%);
        border: none;
        border-radius: 0.75rem;
        padding: 0.875rem 2rem;
        font-weight: 600;
        width: 100%;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }
    
    .btn-register:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(102, 126, 234, 0.3);
    }
    
    .btn-outline-custom {
        border: 2px solid #667eea;
        color: #667eea;
        border-radius: 0.75rem;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-outline-custom:hover {
        background: #667eea;
        color: white;
        transform: translateY(-1px);
    }
    
    .auth-links {
        text-align: center;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e9ecef;
    }
    
    .auth-links a {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s ease;
    }
    
    .auth-links a:hover {
        color: #764ba2;
    }
    
    .invalid-feedback {
        display: block;
        margin-top: 0.5rem;
        font-weight: 500;
    }
    
    @media (max-width: 768px) {
        .auth-container {
            padding: 1rem 0;
        }
        
        .auth-card {
            margin: 0.5rem;
            border-radius: 1rem;
        }
        
        .auth-header {
            border-radius: 1rem 1rem 0 0;
            padding: 1.5rem;
        }
        
        .auth-body {
            padding: 1.5rem;
        }
        
        .auth-header h4 {
            font-size: 1.25rem;
        }
    }
    
    @media (max-width: 576px) {
        .btn-group-responsive {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .btn-group-responsive .btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="background-fade"></div>
<div class="auth-container">
    <div class="container justify-content-center d-flex">
        <div class="auth-card justify-content-center d-flex" style="box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);">
            <div class="col-12 w-50 m-0 justify-content-center d-flex">
                <div class="auth-card m-10 w-100">
                    <div class="auth-body">
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form method="POST" action="#" id="registerForm">
                            @csrf

                            <div class="row">
                                <div class="col-md-6 w-100">
                                    <div class="form-floating">
                                        <input id="email" type="email" 
                                               class="form-control @error('email') is-invalid @enderror" 
                                               name="email" value="{{ old('email') }}" 
                                               required autocomplete="email"
                                               placeholder="Alamat Email">
                                        <label for="email">
                                            <i class="bi bi-envelope-fill me-2"></i>Alamat Email
                                        </label>
                                        @error('email')
                                            <span class="invalid-feedback">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 w-100">
                                    <div class="form-floating">
                                        <input id="nama_user" type="text" 
                                               class="form-control @error('nama_user') is-invalid @enderror" 
                                               name="nama_user" value="{{ old('nama_user') }}" 
                                               required autocomplete="name" autofocus
                                               placeholder="Nama Lengkap">
                                        <label for="nama_user">
                                            <i class="bi bi-person-fill me-2"></i>Nama Lengkap
                                        </label>
                                        @error('nama_user')
                                            <span class="invalid-feedback">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input id="no_telp" type="text" 
                                               class="form-control @error('no_telp') is-invalid @enderror" 
                                               name="no_telp" value="{{ old('no_telp') }}" 
                                               required
                                               placeholder="Nomor Telepon">
                                        <label for="no_telp">
                                            <i class="bi bi-telephone-fill me-2"></i>Nomor Telepon
                                        </label>
                                        @error('no_telp')
                                            <span class="invalid-feedback">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input id="tanggal_lahir" type="date" 
                                               class="form-control @error('tanggal_lahir') is-invalid @enderror" 
                                               name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" 
                                               required
                                               placeholder="Tanggal Lahir">
                                        <label for="tanggal_lahir">
                                            <i class="bi bi-calendar-fill me-2"></i>Tanggal Lahir
                                        </label>
                                        @error('tanggal_lahir')
                                            <span class="invalid-feedback">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input id="password" type="password" 
                                               class="form-control @error('password') is-invalid @enderror" 
                                               name="password" required autocomplete="new-password"
                                               placeholder="Kata Sandi">
                                        <label for="password">
                                            <i class="bi bi-lock-fill me-2"></i>Kata Sandi
                                        </label>
                                        @error('password')
                                            <span class="invalid-feedback">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input id="password_confirmation" type="password" 
                                               class="form-control" 
                                               name="password_confirmation" required autocomplete="new-password"
                                               placeholder="Konfirmasi Kata Sandi">
                                        <label for="password_confirmation">
                                            <i class="bi bi-lock-fill me-2"></i>Konfirmasi Kata Sandi
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Hidden role field - always pelanggan -->
                            <input type="hidden" name="role" value="pelanggan">

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-register btn-primary">
                                    <i class="bi bi-person-plus-fill me-2"></i>
                                    Daftar Akun
                                </button>
                                {{-- <button type="reset" class="fw-bold">
                                    <i class="bi bi-arrow-clockwise me-1"></i>
                                    Reset Form
                                </button> --}}
                            </div>

                            <div class="auth-links">
                                <p class="text-muted mb-2">Sudah memiliki akun?</p>
                                <a href="{{ route('login') }}" class="fw-bold">
                                    <i class="bi bi-box-arrow-in-right me-1"></i>
                                    Masuk di sini
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 w-50">
                <div class="auth-card w-100 m-0 h-100">
                    <div class="auth-body background h-100 d-flex flex-wrap">
                        <div class="text-auth" style="height: 0">
                            <h1 style="background: rgba(50, 48, 48, 0.3); border-radius: 0.9rem; padding: 0.5rem 1rem; backdrop-filter: blur(5px); /* efek kaca */ -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.3); color: #fff; /* teks putih biar kontras */"><b>Registrasi Akun</b></h1>
                        </div>
                        <div class="text-auth">
                            {{-- <img class="img-fluid" style="border-radius: 0.9rem;" src="{{ asset("images/logo-transparan.png") }}" alt="Auth Image"> --}}
                            <h1><b>Hallo!</b><br>Registrasi untuk melanjutkan!</h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation enhancement
    const form = document.getElementById('registerForm');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('password_confirmation');
    
    // Password strength indicator
    password.addEventListener('input', function() {
        const strength = checkPasswordStrength(this.value);
        updatePasswordStrength(strength);
    });
    
    // Password confirmation validation
    confirmPassword.addEventListener('input', function() {
        if (password.value !== this.value) {
            this.setCustomValidity('Kata sandi tidak cocok');
            this.classList.add('is-invalid');
        } else {
            this.setCustomValidity('');
            this.classList.remove('is-invalid');
        }
    });
    
    // Phone number formatting
    const phoneInput = document.getElementById('no_telp');
    phoneInput.addEventListener('input', function() {
        // Remove non-numeric characters
        let value = this.value.replace(/\D/g, '');
        
        // Ensure it starts with 08 if user enters numbers
        if (value.length > 0 && !value.startsWith('08')) {
            if (value.startsWith('8')) {
                value = '0' + value;
            }
        }
        
        // Limit to reasonable phone number length
        if (value.length > 15) {
            value = value.substring(0, 15);
        }
        
        this.value = value;
    });
    
    function checkPasswordStrength(password) {
        let strength = 0;
        if (password.length >= 8) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        return strength;
    }
    
    function updatePasswordStrength(strength) {
        // This could be enhanced with a visual strength indicator
        // For now, we'll just use HTML5 validation
    }
    
    // Enhanced form submission with API call
    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent default form submission
        
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Mendaftar...';
        submitBtn.disabled = true;
        
        // Clear previous errors
        clearValidationErrors();
        
        // Collect form data
        const formData = new FormData(form);
        const data = {
            nama_user: formData.get('nama_user'),
            no_telp: formData.get('no_telp'),
            email: formData.get('email'),
            tanggal_lahir: formData.get('tanggal_lahir'),
            password: formData.get('password'),
            password_confirmation: formData.get('password_confirmation'),
            role: 'pelanggan'
        };
        
        // Debug: log the data being sent
        console.log('Sending registration data:', data);
        
        // Make API call
        fetch('{{ config("services.api.base_url") }}/auth/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        })
        .then(async response => {
            const responseData = await response.json();
            
            if (response.ok) {
                // Registration successful
                showSuccessMessage('Pendaftaran berhasil! Silakan login dengan akun baru Anda.');
                
                // Clear form
                form.reset();
                
                // Redirect to login page after 3 seconds
                setTimeout(() => {
                    window.location.href = '{{ route("login") }}';
                }, 3000);
                
            } else {
                // Handle validation errors
                if (responseData.errors) {
                    displayValidationErrors(responseData.errors);
                } else {
                    showErrorMessage(responseData.message || 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.');
                }
                
                // Re-enable button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Registration error:', error);
            showErrorMessage('Terjadi kesalahan koneksi. Pastikan server API berjalan dan coba lagi.');
            
            // Re-enable button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
    
    function clearValidationErrors() {
        // Remove all existing error messages
        const errorMessages = document.querySelectorAll('.invalid-feedback');
        errorMessages.forEach(msg => msg.remove());
        
        // Remove error classes
        const errorInputs = document.querySelectorAll('.is-invalid');
        errorInputs.forEach(input => input.classList.remove('is-invalid'));
        
        // Remove alert messages
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => alert.remove());
    }
    
    function displayValidationErrors(errors) {
        Object.keys(errors).forEach(field => {
            const input = document.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('is-invalid');
                
                // Create error message
                const errorDiv = document.createElement('span');
                errorDiv.className = 'invalid-feedback';
                errorDiv.innerHTML = `<strong>${errors[field][0]}</strong>`;
                
                // Insert after input
                input.parentNode.appendChild(errorDiv);
            }
        });
    }
    
    function showSuccessMessage(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show';
        alertDiv.setAttribute('role', 'alert');
        alertDiv.innerHTML = `
            <i class="bi bi-check-circle-fill me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insert at the top of auth-body
        const authBody = document.querySelector('.auth-body');
        authBody.insertBefore(alertDiv, authBody.firstChild);
        
        // Scroll to top
        alertDiv.scrollIntoView({ behavior: 'smooth' });
    }
    
    function showErrorMessage(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
        alertDiv.setAttribute('role', 'alert');
        alertDiv.innerHTML = `
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insert at the top of auth-body
        const authBody = document.querySelector('.auth-body');
        authBody.insertBefore(alertDiv, authBody.firstChild);
        
        // Scroll to top
        alertDiv.scrollIntoView({ behavior: 'smooth' });
    }
    
    // Auto-dismiss alerts after 5 seconds
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-close')) {
            setTimeout(() => {
                const alert = e.target.closest('.alert');
                if (alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, 5000);
        }
    });
    
    // Set max date for birth date (must be at least 13 years old)
    const birthDateInput = document.getElementById('tanggal_lahir');
    const today = new Date();
    const maxDate = new Date(today.getFullYear() - 13, today.getMonth(), today.getDate());
    birthDateInput.max = maxDate.toISOString().split('T')[0];
    
    // Set min date (reasonable minimum age of 100 years)
    const minDate = new Date(today.getFullYear() - 100, today.getMonth(), today.getDate());
    birthDateInput.min = minDate.toISOString().split('T')[0];
});
</script>
@endpush
