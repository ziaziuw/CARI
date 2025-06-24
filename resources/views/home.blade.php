@extends('layout.template')

@section('content')
<style>
    .hero-section {
        background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://ubmagerbucket.s3.ap-southeast-1.amazonaws.com/images/bg.webp');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        min-height: 100vh;
        display: flex;
        align-items: center;
        color: white;
    }
    
    .hero-title {
        font-size: 4rem;
        font-weight: bold;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
        margin-bottom: 0.5rem;
        letter-spacing: 2px;
    }
    
    .hero-subtitle {
        font-size: 1.5rem;
        font-weight: 300;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
        margin-bottom: 1rem;
        opacity: 0.9;
    }
    
    .student-info {
        font-size: 0.9rem;
        opacity: 0.8;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
        margin-bottom: 3rem;
        line-height: 1.6;
    }
    
    .register-card {
        background: rgba(254, 249, 217, 0.95);
        backdrop-filter: blur(15px);
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(206, 125, 0, 0.3);
        border: 2px solid rgba(206, 125, 0, 0.2);
        max-width: 500px;
        margin: 0 auto;
        overflow: hidden;
    }
    
    .card-header {
        background: linear-gradient(135deg, #CE7D00 0%, #935900 100%);
        color: #FEF9D9;
        border-radius: 0;
        font-weight: 600;
        text-align: center;
        padding: 1.5rem;
        border-bottom: none;
        position: relative;
    }
    
    .card-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #FEF9D9, #CE7D00, #FEF9D9);
    }
    
    .card-body {
        background: #FEF9D9;
        padding: 2rem;
    }
    
    .form-label {
        color: #935900;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .form-control {
        border-radius: 12px;
        border: 2px solid #CE7D00;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.9);
        color: #935900;
    }
    
    .form-control:focus {
        border-color: #935900;
        box-shadow: 0 0 0 0.25rem rgba(206, 125, 0, 0.25);
        background: #ffffff;
        outline: none;
    }
    
    .form-control::placeholder {
        color: rgba(147, 89, 0, 0.6);
    }
    
    .btn-register {
        background: linear-gradient(135deg, #CE7D00 0%, #935900 100%);
        border: none;
        border-radius: 12px;
        padding: 0.875rem 2rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s ease;
        color: #FEF9D9;
        position: relative;
        overflow: hidden;
    }
    
    .btn-register::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(254, 249, 217, 0.3), transparent);
        transition: left 0.5s;
    }
    
    .btn-register:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(206, 125, 0, 0.4);
        background: linear-gradient(135deg, #935900 0%, #CE7D00 100%);
    }
    
    .btn-register:hover::before {
        left: 100%;
    }
    
    .text-danger {
        color: #d63384 !important;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
    
    .register-card a {
        color: #CE7D00;
        font-weight: 600;
        transition: color 0.3s ease;
    }
    
    .register-card a:hover {
        color: #935900;
        text-decoration: underline !important;
    }
    
    .text-muted {
        color: rgba(147, 89, 0, 0.7) !important;
    }
    
    .fade-in {
        animation: fadeInUp 1s ease-out;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Hover effect untuk card */
    .register-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 25px 50px rgba(206, 125, 0, 0.4);
    }
    
    /* Icon styling */
    .fas {
        color: #CE7D00;
    }
    
    .card-header .fas {
        color: #FEF9D9;
    }
    
    @media (max-width: 768px) {
        .hero-title {
            font-size: 2.5rem;
        }
        .hero-subtitle {
            font-size: 1.2rem;
        }
        .student-info {
            font-size: 0.8rem;
        }
        .register-card {
            margin: 0 1rem;
        }
        .card-body {
            padding: 1.5rem;
        }
    }
</style>

<div class="hero-section" background="url('{{ asset('backround.webp') }}')">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Hero Title -->
                <div class="text-center mb-5 fade-in">
                    <h1 class="hero-title">C.A.R.I. UGM</h1>
                    <p class="hero-subtitle">Community Asset Reporting & Information at UGM</p>
                    
                    <!-- Student Information -->
                    <div class="student-info">
                        <p class="mb-1">Praktikum Pemrograman Geospasial Web Lanjut</p>
                        <p class="mb-1">Adinda Fauzia Azizah | 23/515141/SV/22484</p>
                    </div>
                </div>
                
                @auth
                @else
                <!-- Register Form Card -->
                <div class="fade-in">
                    <div class="card register-card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Daftar Akun Baru</h5>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('register') }}" method="POST">
                                @csrf
                                
                                <!-- Name -->
                                <div class="mb-3">
                                    <label for="name" class="form-label"><i class="fas fa-user me-2"></i>Nama Lengkap</label>
                                    <input type="text" class="form-control" id="name" name="name" required value="{{ old('name') }}" placeholder="Masukkan nama lengkap">
                                    @error('name') <span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</span> @enderror
                                </div>
                                
                                <!-- Email -->
                                <div class="mb-3">
                                    <label for="email" class="form-label"><i class="fas fa-envelope me-2"></i>Alamat Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required value="{{ old('email') }}" placeholder="contoh@mail.ugm.ac.id">
                                    @error('email') <span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</span> @enderror
                                </div>
                                
                                <!-- Password -->
                                <div class="mb-3">
                                    <label for="password" class="form-label"><i class="fas fa-lock me-2"></i>Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required placeholder="Minimal 8 karakter">
                                    @error('password') <span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</span> @enderror
                                </div>
                                
                                <!-- Confirm Password -->
                                <div class="mb-4">
                                    <label for="password_confirmation" class="form-label"><i class="fas fa-lock me-2"></i>Konfirmasi Password</label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required placeholder="Ulangi password">
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-register">
                                        <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                                    </button>
                                </div>
                            </form>
                            
                            <div class="text-center mt-3">
                                <small class="text-muted">Sudah punya akun? <a href="{{ route('login') }}" class="text-decoration-none">Masuk di sini</a></small>
                            </div>
                        </div>
                    </div>
                </div>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection