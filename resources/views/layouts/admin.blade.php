<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Monitoring Sensus Ekonomi</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        body { background-color: #f8f9fa; margin: 0; display: flex; font-family: 'Segoe UI', sans-serif; }
        
        /* SIDEBAR DESKTOP (layar lebar > 768px) */
        .sidebar { 
            width: 250px; 
            min-width: 250px; 
            height: 100vh; 
            background-color: #212529; 
            color: white; 
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }

        .scrollable-menu::-webkit-scrollbar {
            width: 6px;
        }
        .scrollable-menu::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.1); 
        }
        .scrollable-menu::-webkit-scrollbar-thumb {
            background: #495057; 
            border-radius: 10px;
        }
        .scrollable-menu::-webkit-scrollbar-thumb:hover {
            background: #6c757d; 
        }
        
        .sidebar a { 
            color: #adb5bd; 
            text-decoration: none; 
            padding: 15px 20px; 
            display: block; 
            border-bottom: 1px solid #343a40; 
            transition: 0.3s;
        }
        
        .sidebar a:hover, .sidebar a.active { 
            background-color: #0d6efd; 
            color: white; 
            border-left: 5px solid #fff; 
        }
        
        /* AREA KONTEN UTAMA */
        .content { 
            width: 100%; 
            padding: 30px; 
            overflow-x: hidden; 
        }

        /* NAVBAR MOBILE (di layar kecil < 768px) */
        .mobile-nav { 
            display: none; 
            background-color: #212529; 
            padding: 12px 20px; 
            width: 100%; 
            position: sticky;
            top: 0;
            z-index: 1050;
        }

        /* RESPONSIVE LOGIC */
        @media (max-width: 768px) {
            body { flex-direction: column; }
            .sidebar { display: none; } /* Sembunyikan sidebar samping di HP */
            .mobile-nav { display: flex; justify-content: space-between; align-items: center; }
            .content { padding: 15px; }
        }

        /* STYLING MENU OFFCANVAS  */
        .offcanvas { background-color: #212529; width: 280px !important; }
        .offcanvas-header { border-bottom: 1px solid #343a40; }
        .offcanvas-body p { color: #6c757d; font-size: 0.75rem; padding-left: 20px; }
        .offcanvas a { 
            color: #adb5bd; 
            text-decoration: none; 
            padding: 15px 20px; 
            display: block; 
            border-bottom: 1px solid #343a40; 
        }
        .offcanvas a:hover { background-color: #0d6efd; color: white; }

        .collapse-item:hover {
            background-color: rgba(255, 255, 255, 0.1) !important;
            color: white !important;
            border-radius: 5px;
        }
        .nav-link.active {
            background-color: rgba(255,255,255,0.1);
            border-left: 4px solid #fff !important;
        }
    </style>
</head>
<body>

    <div class="mobile-nav shadow-sm">
        <div class="d-flex align-items-center text-white">
            <i class="fas fa-chart-line me-2 text-primary"></i>
            <h6 class="mb-0 fw-bold">Monitoring Sensus Ekonomi</h6>
        </div>
        <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <div class="sidebar">
        <div>
            <div class="p-4 text-center border-bottom border-secondary bg-dark">
                <h5 class="fw-bold mb-0 text-white">            
                    <i class="fas fa-chart-line text-primary"></i>
                    MONITORING SE</h5>
            </div>
            
            <div class="p-3 border-bottom text-center" style="background-color: rgba(0,0,0,0.1);">
                <div class="fw-bold text-white mb-1"><i class="fas fa-user-circle fs-2 text-secondary mb-2"></i><br>
                    {{ auth()->user()->name ?? 'Nama Pengguna' }}
                </div>
                <div class="badge bg-primary text-uppercase mb-2">{{ auth()->user()->role ?? 'Admin' }}</div>
                
                @if(in_array(auth()->user()->role ?? '', ['pml', 'koseka', 'petugas']))
                    <div class="small text-muted mt-1" style="font-size: 0.75rem;">
                        <i class="fas fa-map-marker-alt text-danger me-1"></i> Area Kerja: <br>
                        <strong class="text-light">{{ auth()->user()->wilayah_kerja ?? 'Kecamatan Lengkiti' }}</strong>
                    </div>
                @endif
            </div>
        </div>

        <div class="scrollable-menu flex-grow-1" style="overflow-y: auto; overflow-x: hidden;">
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard Wilayah
            </a>

            @if(auth()->check() && auth()->user()->role == 'admin')
                <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="fas fa-users-cog me-2"></i> Kelola User
                </a>
                <li class="nav-item border-start border-3 border-transparent" id="nav-master" style="list-style: none;">
                    <a class="nav-link collapsed d-flex align-items-center justify-content-between text-decoration-none" 
                       href="#" style="color: #adb5bd; padding: 15px 20px; border-bottom: 1px solid #343a40;"
                       data-bs-toggle="collapse" 
                       data-bs-target="#collapseMaster"
                       aria-expanded="false" 
                       aria-controls="collapseMaster">
                        <span>
                            <i class="fas fa-fw fa-folder-open me-2"></i>
                            <span class="fw-bold">Data Master</span>
                        </span>
                        <i class="fas fa-chevron-right fa-xs"></i>
                    </a>
                    <div id="collapseMaster" class="collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionSidebar">
                        <div class="py-2 collapse-inner rounded mx-2 mt-1 border-top-0 border-end-0 border-bottom-0 border-start border-3 border-primary" style="background-color: rgba(0,0,0,0.2); outline: none;">
                            <a class="collapse-item d-block p-2 px-3 text-decoration-none small text-light" 
                               href="{{ route('master.dataset') }}" style="border-bottom: none; padding: 10px 20px !important;">
                               <i class="fas fa-database me-2 text-success"></i>Dataset SubSLS
                            </a>
                            <a class="collapse-item d-block p-2 px-3 text-decoration-none small text-light" 
                               href="{{ route('master.wilayah') }}" style="border-bottom: none; padding: 10px 20px !important;">
                               <i class="fas fa-map-marked-alt me-2 text-primary"></i>Wilayah Master
                            </a>
                            <a class="collapse-item d-block p-2 px-3 text-decoration-none small text-light" 
                               href="{{ route('master.kendala') }}" style="border-bottom: none; padding: 10px 20px !important;">
                               <i class="fas fa-exclamation-triangle me-2 text-warning"></i>Bobot Kendala
                            </a>
                        </div>
                    </div>
                </li>
            @endif

            <a href="{{ route('hasil.klaster') }}" class="{{ request()->routeIs('hasil.klaster') ? 'active' : '' }}">
                <i class="fas fa-robot me-2"></i> Hasil Klaster
            </a>
        </div>
        
        <div class="p-3 bg-dark border-top border-secondary mt-auto">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-danger w-100 btn-sm fw-bold shadow-sm">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout Sistem
                </button>
            </form>
        </div>
    </div>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasMenu">
        <div class="offcanvas-header text-white bg-dark">
            <h5 class="offcanvas-title fw-bold">NAVIGASI MENU</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        
        <div class="offcanvas-body p-0 d-flex flex-column">
            <div>
                <div class="p-3 border-bottom text-center" style="background-color: rgba(0,0,0,0.1);">
                    <div class="fw-bold text-white mb-1"><i class="fas fa-user-circle fs-2 text-secondary mb-2"></i><br>
                        {{ auth()->user()->name ?? 'Nama Pengguna' }}
                    </div>
                    <div class="badge bg-primary text-uppercase mb-2">{{ auth()->user()->role ?? 'Admin' }}</div>
                    @if(in_array(auth()->user()->role ?? '', ['pml', 'koseka', 'petugas']))
                        <div class="small text-muted mt-1" style="font-size: 0.75rem;">
                            <i class="fas fa-map-marker-alt text-danger me-1"></i> Area Kerja: <br>
                            <strong class="text-light">{{ auth()->user()->wilayah_kerja ?? 'Kecamatan Lengkiti' }}</strong>
                        </div>
                    @endif
                </div>
            </div>

            <div class="scrollable-menu flex-grow-1" style="overflow-y: auto; overflow-x: hidden;">
                <a href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt me-2"></i> Dashboard Wilayah</a>
                
                @if(auth()->check() && auth()->user()->role == 'admin')
                    <a href="{{ route('users.index') }}"><i class="fas fa-users-cog me-2"></i> Kelola User</a>
                    
                    <a data-bs-toggle="collapse" href="#collapseMasterMobile" role="button" aria-expanded="false" aria-controls="collapseMasterMobile" class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-folder-open me-2"></i> Data Master</span>
                        <i class="fas fa-chevron-down fa-xs"></i>
                    </a>
                    <div class="collapse" id="collapseMasterMobile">
                        <div class="border-start border-3 border-primary ms-3 my-2" style="background-color: rgba(0,0,0,0.2);">
                            <a href="{{ route('master.dataset') }}" class="py-2" style="font-size: 0.85rem; border-bottom: none;">
                                <i class="fas fa-database me-2 text-success"></i>Dataset SubSLS
                            </a>
                            <a href="{{ route('master.wilayah') }}" class="py-2" style="font-size: 0.85rem; border-bottom: none;">
                                <i class="fas fa-map-marked-alt me-2 text-primary"></i>Wilayah Master
                            </a>
                            <a href="{{ route('master.kendala') }}" class="py-2" style="font-size: 0.85rem; border-bottom: none;">
                                <i class="fas fa-exclamation-triangle me-2 text-warning"></i>Bobot Kendala
                            </a>
                        </div>
                    </div>
                    <a href="{{ route('hasil.klaster') }}"><i class="fas fa-robot me-2"></i> Hasil Klaster AI</a>
                @endif
            </div>
            
            <div class="p-3 mt-auto bg-dark border-top border-secondary">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger w-100 fw-bold shadow-sm">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout Sistem
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="content">
        @yield('content')
    </div>

    <script>
        if (window.location.href.indexOf("master") > -1) {
            document.getElementById("collapseMaster").classList.add("show");
            document.getElementById("nav-master").classList.add("active");
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>