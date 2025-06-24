<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            body {
                background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://ubmagerbucket.s3.ap-southeast-1.amazonaws.com/images/bg.webp');
                background-size: cover;
                background-position: center;
                background-attachment: fixed;
                min-height: 100vh;
                font-family: 'Figtree', sans-serif;
                color: #FEF9D9;
            }
            
            .guest-container {
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                padding: 1.5rem;
                position: relative;
            }
            
            .guest-container::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="20" cy="20" r="1" fill="%23FEF9D9" opacity="0.1"/><circle cx="80" cy="80" r="1" fill="%23FEF9D9" opacity="0.1"/><circle cx="40" cy="60" r="1" fill="%23FEF9D9" opacity="0.05"/><circle cx="60" cy="40" r="1" fill="%23FEF9D9" opacity="0.05"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
                pointer-events: none;
            }
            
            .logo-container {
                margin-bottom: 2rem;
                text-align: center;
                z-index: 1;
                position: relative;
            }
            
            .app-logo {
                width: 80px;
                height: 80px;
                background: linear-gradient(135deg, #CE7D00 0%, #935900 100%);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 1rem;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
                transition: transform 0.3s ease;
            }
            
            .app-logo:hover {
                transform: scale(1.05) rotate(5deg);
            }
            
            .app-logo i {
                font-size: 2rem;
                color: #FEF9D9;
            }
            
            .app-title {
                color: #FEF9D9;
                font-size: 1.5rem;
                font-weight: bold;
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
                margin: 0;
            }
            
            .form-container {
                width: 100%;
                max-width: 450px;
                background: rgba(254, 249, 217, 0.9);
                backdrop-filter: blur(15px);
                border-radius: 20px;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
                border: 2px solid rgba(206, 125, 0, 0.5);
                padding: 2.5rem;
                position: relative;
                z-index: 1;
                overflow: hidden;
            }
            
            .form-container::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 4px;
                background: linear-gradient(90deg, #CE7D00 0%, #935900 50%, #CE7D00 100%);
            }
            
            /* Tambahkan efek glow pada form container */
            .form-container {
                animation: glow 3s infinite alternate;
            }
            
            @keyframes glow {
                from {
                    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
                }
                to {
                    box-shadow: 0 20px 50px rgba(206, 125, 0, 0.5);
                }
            }
            
            @media (max-width: 640px) {
                .form-container {
                    margin: 1rem;
                    padding: 2rem;
                }
                
                .app-logo {
                    width: 60px;
                    height: 60px;
                }
                
                .app-logo i {
                    font-size: 1.5rem;
                }
                
                .app-title {
                    font-size: 1.2rem;
                }
            }
        </style>
    </head>
    <body>
        <div class="guest-container">
            <div class="logo-container">
                <a href="/">
                    <div class="app-logo">
                        <i class="fas fa-earth-asia"></i>
                    </div>
                    <h1 class="app-title">C.A.R.I. UGM</h1>
                </a>
            </div>

            <div class="form-container">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
