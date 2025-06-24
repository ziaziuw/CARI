<x-guest-layout>
    <style>
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-title {
            color: #935900;
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            text-shadow: 1px 1px 2px rgba(147, 89, 0, 0.1);
        }
        
        .login-subtitle {
            color: #CE7D00;
            font-size: 1rem;
            opacity: 0.8;
            margin-bottom: 0;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            color: #935900 !important;
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        input[type="email"],
        input[type="password"] {
            width: 100%;
            border: 2px solid #CE7D00 !important;
            border-radius: 10px !important;
            padding: 0.875rem 1rem !important;
            transition: all 0.3s ease !important;
            background-color: #FEF9D9 !important;
            font-size: 1rem;
            color: #935900;
        }
        
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #935900 !important;
            box-shadow: 0 0 0 4px rgba(206, 125, 0, 0.2) !important;
            outline: none !important;
            background-color: #ffffff !important;
        }
        
        input[type="checkbox"] {
            accent-color: #CE7D00;
            width: 1.2rem;
            height: 1.2rem;
        }
        
        .checkbox-container {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
        }
        
        .checkbox-label {
            color: #935900 !important;
            margin-left: 0.5rem;
            font-size: 0.9rem;
        }
        
        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
        }
        
        .forgot-password-link {
            color: #CE7D00 !important;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .forgot-password-link:hover {
            color: #935900 !important;
            text-decoration: underline;
        }
        
        .login-button {
            background: linear-gradient(135deg, #CE7D00 0%, #935900 100%) !important;
            border: none !important;
            border-radius: 10px !important;
            padding: 0.875rem 2rem !important;
            color: #FEF9D9 !important;
            font-weight: 600 !important;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease !important;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(206, 125, 0, 0.4) !important;
        }
        
        .error-message {
            color: #dc3545;
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            border-radius: 8px;
            padding: 0.5rem;
            margin-top: 0.5rem;
            font-size: 0.875rem;
        }
        
        .status-message {
            background-color: rgba(206, 125, 0, 0.1);
            border: 1px solid rgba(206, 125, 0, 0.3);
            color: #935900;
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        
        @media (max-width: 640px) {
            .form-actions {
                flex-direction: column;
                gap: 1rem;
            }
            
            .login-button {
                width: 100%;
            }
        }
    </style>
    
    <div class="login-header">
        <h2 class="login-title">Selamat Datang</h2>
        <p class="login-subtitle">Masuk ke akun C.A.R.I. UGM Anda</p>
    </div>
    
    <!-- Session Status -->
    <x-auth-session-status class="status-message" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="form-group">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="error-message" />
        </div>

        <!-- Password -->
        <div class="form-group">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="error-message" />
        </div>

        <!-- Remember Me -->
        <div class="checkbox-container">
            <input id="remember_me" type="checkbox" name="remember">
            <label for="remember_me" class="checkbox-label">{{ __('Remember me') }}</label>
        </div>

        <div class="form-actions">
            @if (Route::has('password.request'))
                <a class="forgot-password-link" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="login-button">
                <i class="fas fa-sign-in-alt me-2"></i>{{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
