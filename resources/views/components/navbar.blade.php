<style>
    .navbar {
        background: linear-gradient(135deg, #CE7D00 0%, #935900 100%) !important;
        box-shadow: 0 2px 10px rgba(147, 89, 0, 0.3);
    }
    
    .navbar-brand {
        color: #FEF9D9 !important;
        font-weight: bold;
        font-size: 1.3rem;
    }
    
    .navbar-brand:hover {
        color: #FEF9D9 !important;
        text-shadow: 0 0 10px rgba(254, 249, 217, 0.5);
    }
    
    .nav-link {
        color: #FEF9D9 !important;
        transition: all 0.3s ease;
        border-radius: 5px;
        margin: 0 2px;
    }
    
    .nav-link:hover {
        color: #FEF9D9 !important;
        background-color: rgba(254, 249, 217, 0.2);
        transform: translateY(-1px);
    }
    
    .dropdown-menu {
        background-color: #FEF9D9;
        border: 2px solid #CE7D00;
        box-shadow: 0 5px 15px rgba(147, 89, 0, 0.3);
    }
    
    .dropdown-item {
        color: #935900 !important;
        transition: all 0.3s ease;
    }
    
    .dropdown-item:hover {
        background-color: #CE7D00;
        color: #FEF9D9 !important;
    }
    
    .navbar-toggler {
        border-color: #FEF9D9;
    }
    
    .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='%23FEF9D9' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='m4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }
    
    .text-danger {
        color: #dc3545 !important;
        background: none;
        border: none;
        padding: 0.5rem 1rem;
    }
    
    .text-danger:hover {
        background-color: rgba(220, 53, 69, 0.1);
        border-radius: 5px;
    }
    
    .text-primary {
        color: #FEF9D9 !important;
        background-color: rgba(254, 249, 217, 0.1);
        border-radius: 5px;
    }
    
    .text-primary:hover {
        background-color: rgba(254, 249, 217, 0.2);
    }
</style>

<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="#"><i class="fa-solid fa-earth-asia"></i> {{ $title }}</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home') }}"><i class="fa-solid fa-house-chimney"></i> Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('map') }}"><i class="fa-solid fa-map-location-dot"></i> Peta</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('table') }}"><i class="fa-solid fa-table"></i> Tabel</a>
                </li>
                @auth
                    {{-- @auth memunculkan dropdown data hanya saat pengguna sudah login --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-database"></i> Data
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('api.points') }}" target="_blank"><i
                                        class="fa-solid fa-location-dot"></i> Data Laporan</a></li>
                        </ul>
                    </li>

                    {{-- @auth memunculkan tombol logout hanya saat pengguna sudah login --}}
                    <li class="nav-item">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="nav-link text-danger" type="submit">
                                <i class="fa-solid fa-right-from-bracket"></i>
                                Logout</button>
                        </form>
                    </li>
                @endauth

                {{-- jika blm login maka muncul tombol login --}}
                @guest
                    <li class="nav-item">
                        <a class="nav-link text-primary" href="{{ route('login') }}">
                            <i class="fa-solid fa-right-to-bracket"></i>
                            Login</a>
                        </form>
                    </li>
                @endguest
            </ul>
            </li>
            </ul>
        </div>
    </div>
</nav>
