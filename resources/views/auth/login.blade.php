@extends('layouts.app')

@section('title', 'Login')

@push('styles')
<style>
    .auth-container {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 0;
    }

    .dp-grid {
        display: grid;
    }
    
    .auth-card {
        background: white;
        border-radius: 1.5rem;
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
        border: none;
        max-width: 450px;
        width: 100%;
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
    }
    
    .form-floating {
        margin-bottom: 1.5rem;
    }
    
    .form-floating .form-control {
        border-radius: 0.75rem;
        border: 2px solid #e9ecef;
        padding: 1rem 0.75rem;
        height: calc(3.5rem + 2px);
        transition: all 0.3s ease;
    }
    
    .form-floating .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .form-floating label {
        color: #6c757d;
        font-weight: 500;
    }
    
    .btn-login {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 0.75rem;
        padding: 0.875rem 2rem;
        font-weight: 600;
        width: 100%;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }
    
    .btn-login:hover {
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
        margin-bottom: 0.5rem;
    }
    
    .btn-outline-custom:hover {
        background: #667eea;
        color: white;
        transform: translateY(-1px);
    }
    
    .btn-outline-warning-custom {
        border: 2px solid #ffc107;
        color: #ffc107;
        border-radius: 0.75rem;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
        margin-bottom: 0.5rem;
    }
    
    .btn-outline-warning-custom:hover {
        background: #ffc107;
        color: #000;
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
    
    .form-check {
        margin: 1rem 0;
    }
    
    .form-check-input:checked {
        background-color: #667eea;
        border-color: #667eea;
    }
    
    .form-check-input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
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
<div class="auth-container">
    <div class="container">
        <div class="row justify-content-center dp-grid">
            <div class="col-12">
                <div class="auth-card">
                    <div class="auth-header">
                        <i class="bi bi-hospital-fill fs-1 mb-3"></i>
                        <h4>Welcome Back</h4>
                        <p class="mb-0 opacity-75">Sign in to your account</p>
                    </div>

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

                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                {{ session('status') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" id="loginForm">
                            @csrf

                            <div class="form-floating">
                                <input id="email" type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       name="email" value="{{ old('email') }}" 
                                       required autocomplete="email" autofocus
                                       placeholder="Email Address">
                                <label for="email">
                                    <i class="bi bi-envelope-fill me-2"></i>Email Address
                                </label>
                                @error('email')
                                    <span class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-floating">
                                <input id="password" type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       name="password" required autocomplete="current-password"
                                       placeholder="Password">
                                <label for="password">
                                    <i class="bi bi-lock-fill me-2"></i>Password
                                </label>
                                @error('password')
                                    <span class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" 
                                       {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    <i class="bi bi-check2-square me-1"></i>
                                    {{ __('Remember Me') }}
                                </label>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-login btn-primary">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>
                                    Sign In
                                </button>
                                
                                <div class="btn-group-responsive d-flex justify-content-between gap-2">
                                    @if (Route::has('password.request'))
                                        <a class="btn btn-outline-warning-custom flex-fill" href="{{ route('password.request') }}">
                                            <i class="bi bi-key me-1"></i>
                                            Forgot Password?
                                        </a>
                                    @endif
                                    
                                    @if (Route::has('register'))
                                        <a class="btn btn-outline-custom flex-fill" href="{{ route('register') }}">
                                            <i class="bi bi-person-plus me-1"></i>
                                            Create Account
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <div class="auth-links">
                                <p class="text-muted mb-2">Don't have an account?</p>
                                <a href="{{ route('register') }}" class="fw-bold">
                                    <i class="bi bi-person-plus-fill me-1"></i>
                                    Register here
                                </a>
                            </div>
                        </form>
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
    const form = document.getElementById('loginForm');
    
    // Enhanced form submission
    form.addEventListener('submit', function(e) {
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Signing In...';
        submitBtn.disabled = true;
        
        // Re-enable button after 5 seconds if form submission fails
        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 5000);
    });
    
    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert && !alert.classList.contains('d-none')) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    });
    
    // Password visibility toggle (can be added later)
    // Add eye icon to toggle password visibility
});
</script>
@endpush
