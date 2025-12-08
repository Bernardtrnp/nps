<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Nusantara Power Service Dashboard</title>

    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @yield('head') <!-- Tambahkan ini supaya card summary di dashboard muncul-->
    @yield('styles')

    <style>
        body { background: #f6f8fa; }

        /* NAVBAR — teal seperti contoh Anda */
        .navbar-teal {
            background: linear-gradient(90deg, #00a3b0 0%, #0099a8 100%);
            box-shadow: 0 6px 18px rgba(4,30,40,0.15);
            padding: 0 0;
        }

        /* GANTI bagian logo di CSS dengan ini */
        .navbar-inner {
            display: flex;
            align-items: center;   /* center vertical */
            width: 100%;
            height: 70px;         /* tinggi navbar — sesuaikan jika Anda ubah */
            overflow: visible;
        }

        /* Brand wrapper (posisi di dalam navbar, tidak keluar) */
        .navbar-brand {
            display: flex;
            align-items: center;
            padding-left: 20px;
            padding-right: 12px;
            height: 100%;          /* agar mengikuti tinggi navbar */
            z-index: 5;
        }

        /* Kotak putih di belakang logo: jangan lebih tinggi dari navbar */
        .navbar-brand .logo-wrapper {
            background: #ffffff;
            padding: 8px 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 14px rgba(2,6,23,0.06); /* lembut, tidak terlalu besar */
            margin-left: 4px;
        }

        /* Logo sendiri (besar tapi tetap proporsional) */
        .navbar-brand img {
            height: 54px;   /* besar logo — ubah ke 60/64 jika mau, tapi sesuaikan height navbar */
            width: auto;
            object-fit: contain;
            display: block;
        }

        /* Jika navbar dibuat lebih pendek/taller, adjust: 
        - jika height navbar < 70px, turunkan logo height; 
        - jika ingin logo lebih besar, naikkan navbar height juga. */


        /* NAV LINKS DI KANAN */
        .nav-links {
            margin-left: auto;
            display: flex;
            gap: 26px;
        }

        .nav-links .nav-link {
            color: #ffffff;
            font-weight: 500;
            padding: 14px 8px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .nav-links .nav-link:hover {
            background: rgba(255,255,255,0.12);
            color: #fff;
        }

        .nav-links .nav-link.active {
            background: rgba(255,255,255,0.18);
            box-shadow: 0 4px 14px rgba(0,0,0,0.12);
        }

        main.container { margin-top: 12px; }

        /* Select2 Fix */
        .select2-container .select2-selection--single,
        .select2-container .select2-selection--multiple {
            min-height: 38px;
            padding: 4px 8px;
        }
    </style>

</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-teal mb-4">
        <div class="container-fluid">

            <div class="navbar-inner">

                <!-- LOGO LANGSUNG TANPA WHITE BOX -->
                <a class="navbar-brand" href="{{ route('risk.home') }}">
                    <span class="logo-wrapper">
                        <img src="{{ asset('images/nusantara-logo.png') }}" alt="Logo Nusantara">
                    </span>
                </a>

                <!-- NAV LINKS -->
                <div class="nav-links">
                    <!-- HOME -->
                    <a class="nav-link {{ request()->routeIs('risk.home') ? 'active' : '' }}"
                        href="{{ route('risk.home') }}">
                        <i class="bi bi-house-door-fill"></i>
                        <span class="d-none d-md-inline">Home</span>
                    </a>

                    <!-- RISK SUMMARY (NEW) -->
                    <a class="nav-link {{ request()->routeIs('risk.summary') ? 'active' : '' }}"
                        href="{{ route('risk.summary') }}">
                        <i class="bi bi-award-fill"></i>
                        Summary Risiko
                    </a>

                    <!-- OVERVIEW RISIKO -->
                    <a class="nav-link {{ request()->routeIs('risk.dashboard') ? 'active' : '' }}"
                    href="{{ route('risk.dashboard') }}">
                        <i class="bi bi-bar-chart-line-fill"></i>
                        Overview Risiko
                    </a>

                    <!-- ANALISIS -->
                    <a class="nav-link {{ request()->routeIs('risk.analysis') ? 'active' : '' }}"
                    href="{{ route('risk.analysis') }}">
                        <i class="bi bi-graph-up"></i>
                        Analisis
                    </a>

                    <!-- UPLOAD -->
                    <a class="nav-link {{ request()->routeIs('risk.import.form') ? 'active' : '' }}"
                    href="{{ route('risk.import.form') }}">
                        <i class="bi bi-cloud-upload-fill"></i>
                        Upload Data
                    </a>

                </div>
            </div>

        </div>
    </nav>


    <!-- MAIN CONTENT -->
    <main class="container">
        @yield('intro')
        @yield('content')
    </main>

    <!-- jQuery harus paling atas -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Select2 (harus setelah jQuery) -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @yield('scripts')
</body>
</html>
