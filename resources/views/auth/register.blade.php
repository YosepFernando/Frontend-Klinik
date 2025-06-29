@extends('layouts.app')

@section('title', 'Register')

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
        max-width: 600px;
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
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    
    .role-info {
        background: #f8f9fa;
        border-radius: 0.75rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
        border-left: 4px solid #667eea;
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
                        <i class="bi bi-person-plus-fill fs-1 mb-3"></i>
                        <h4>Create Your Account</h4>
                        <p class="mb-0 opacity-75">Join our clinic management system</p>
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

                        <div class="role-info">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                <small class="text-muted">
                                    <strong>Note:</strong> Choose your role carefully. This determines your access level in the system.
                                </small>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('register') }}" id="registerForm">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input id="name" type="text" 
                                               class="form-control @error('name') is-invalid @enderror" 
                                               name="name" value="{{ old('name') }}" 
                                               required autocomplete="name" autofocus
                                               placeholder="Full Name">
                                        <label for="name">
                                            <i class="bi bi-person-fill me-2"></i>Full Name
                                        </label>
                                        @error('name')
                                            <span class="invalid-feedback">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input id="email" type="email" 
                                               class="form-control @error('email') is-invalid @enderror" 
                                               name="email" value="{{ old('email') }}" 
                                               required autocomplete="email"
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
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input id="password" type="password" 
                                               class="form-control @error('password') is-invalid @enderror" 
                                               name="password" required autocomplete="new-password"
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
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input id="password-confirm" type="password" 
                                               class="form-control" 
                                               name="password_confirmation" required autocomplete="new-password"
                                               placeholder="Confirm Password">
                                        <label for="password-confirm">
                                            <i class="bi bi-lock-fill me-2"></i>Confirm Password
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input id="phone" type="text" 
                                               class="form-control @error('phone') is-invalid @enderror" 
                                               name="phone" value="{{ old('phone') }}" 
                                               placeholder="Phone Number">
                                        <label for="phone">
                                            <i class="bi bi-telephone-fill me-2"></i>Phone Number
                                        </label>
                                        @error('phone')
                                            <span class="invalid-feedback">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select id="gender" 
                                                class="form-select @error('gender') is-invalid @enderror" 
                                                name="gender">
                                            <option value="">Select gender...</option>
                                            <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                            <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                        </select>
                                        <label for="gender">
                                            <i class="bi bi-gender-ambiguous me-2"></i>Gender
                                        </label>
                                        @error('gender')
                                            <span class="invalid-feedback">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-floating">
                                <select id="role" 
                                        class="form-select @error('role') is-invalid @enderror" 
                                        name="role" required>
                                    <option value="">Choose your role...</option>
                                    <option value="pelanggan" {{ old('role') == 'pelanggan' ? 'selected' : '' }}>
                                        👤 Customer (Pelanggan)
                                    </option>
                                    <option value="front_office" {{ old('role') == 'front_office' ? 'selected' : '' }}>
                                        🏢 Front Office
                                    </option>
                                    <option value="kasir" {{ old('role') == 'kasir' ? 'selected' : '' }}>
                                        💰 Cashier (Kasir)
                                    </option>
                                    <option value="beautician" {{ old('role') == 'beautician' ? 'selected' : '' }}>
                                        💄 Beautician
                                    </option>
                                    <option value="dokter" {{ old('role') == 'dokter' ? 'selected' : '' }}>
                                        👨‍⚕️ Doctor (Dokter)
                                    </option>
                                    <option value="hrd" {{ old('role') == 'hrd' ? 'selected' : '' }}>
                                        👥 Human Resources (HRD)
                                    </option>
                                </select>
                                <label for="role">
                                    <i class="bi bi-person-badge-fill me-2"></i>Register As
                                </label>
                                @error('role')
                                    <span class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-register btn-primary">
                                    <i class="bi bi-person-plus-fill me-2"></i>
                                    Create Account
                                </button>
                                
                                <div class="btn-group-responsive d-flex justify-content-between">
                                    <button type="reset" class="btn btn-outline-custom flex-fill me-2">
                                        <i class="bi bi-arrow-clockwise me-1"></i>
                                        Reset Form
                                    </button>
                                    <a href="{{ route('login') }}" class="btn btn-outline-custom flex-fill">
                                        <i class="bi bi-arrow-left me-1"></i>
                                        Back to Login
                                    </a>
                                </div>
                            </div>

                            <div class="auth-links">
                                <p class="text-muted mb-2">Already have an account?</p>
                                <a href="{{ route('login') }}" class="fw-bold">
                                    <i class="bi bi-box-arrow-in-right me-1"></i>
                                    Sign in here
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
    // Form validation enhancement
    const form = document.getElementById('registerForm');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('password-confirm');
    
    // Password strength indicator
    password.addEventListener('input', function() {
        const strength = checkPasswordStrength(this.value);
        updatePasswordStrength(strength);
    });
    
    // Password confirmation validation
    confirmPassword.addEventListener('input', function() {
        if (password.value !== this.value) {
            this.setCustomValidity('Passwords do not match');
            this.classList.add('is-invalid');
        } else {
            this.setCustomValidity('');
            this.classList.remove('is-invalid');
        }
    });
    
    // Phone number formatting
    const phoneInput = document.getElementById('phone');
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
    
    // Enhanced form submission
    form.addEventListener('submit', function(e) {
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Creating Account...';
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
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
</script>
@endpush
