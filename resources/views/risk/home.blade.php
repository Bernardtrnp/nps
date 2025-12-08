@extends('layouts.app')

@section('intro')
<div class="mb-4">
    <h2 class="fw-bold">Selamat datang di Dashboard Risiko Nusantara Power Services</h2>
    <p class="text-muted">
        Platform ini membantu Anda mengunggah, meninjau, merangkum, dan menganalisis risiko
        secara menyeluruh. Ikuti langkah-langkah berikut untuk alur kerja yang optimal.
    </p>
</div>
@endsection

@section('content')
<style>
    .step-card {
        border-left: 6px solid #0d6efd;
        border-radius: 10px;
        background: #ffffff;
        box-shadow: 0 3px 12px rgba(0,0,0,0.06);
        transition: 0.2s ease;
    }
    .step-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.10);
    }
    .step-number {
        font-size: 32px;
        font-weight: 800;
        color: #0d6efd;
    }
    .step-icon {
        font-size: 28px;
        color: #0d6efd;
        margin-right: 8px;
    }
</style>

<div class="row g-4">

    <!-- STEP 1 -->
    <div class="col-md-12">
        <div class="card step-card p-3">
            <div class="row align-items-center">
                <div class="col-md-1 text-center">
                    <div class="step-number">1</div>
                </div>
                <div class="col-md-8">
                    <h5 class="d-flex align-items-center">
                        <i class="bi bi-cloud-upload-fill step-icon"></i>
                        Upload Data Risiko
                    </h5>
                    <p class="text-muted">Mulai dengan mengunggah file Excel berisi data risiko dari unit Anda.</p>
                </div>
                <div class="col-md-3 text-md-end">
                    <a href="{{ route('risk.import.form') }}" class="btn btn-primary">Unggah Data</a>
                </div>
            </div>
        </div>
    </div>

    <!-- STEP 2 - SUMMARY -->
    <div class="col-md-12">
        <div class="card step-card p-3">
            <div class="row align-items-center">
                <div class="col-md-1 text-center">
                    <div class="step-number">2</div>
                </div>
                <div class="col-md-8">
                    <h5 class="d-flex align-items-center">
                        <i class="bi bi-award-fill step-icon"></i>
                        Summary Risiko (Ranking)
                    </h5>
                    <p class="text-muted">
                        Lihat peringkat risiko otomatis berdasarkan simulasi statistik dan CVaR.
                        Menampilkan top risk untuk seluruh jenis risiko.
                    </p>
                </div>
                <div class="col-md-3 text-md-end">
                    <a href="{{ route('risk.summary') }}" class="btn btn-outline-primary">Lihat Summary</a>
                </div>
            </div>
        </div>
    </div>

    <!-- STEP 3 - OVERVIEW -->
    <div class="col-md-12">
        <div class="card step-card p-3">
            <div class="row align-items-center">
                <div class="col-md-1 text-center">
                    <div class="step-number">3</div>
                </div>
                <div class="col-md-8">
                    <h5 class="d-flex align-items-center">
                        <i class="bi bi-bar-chart-line-fill step-icon"></i>
                        Overview Risiko
                    </h5>
                    <p class="text-muted">Tinjau ringkasan risiko, statistik dasar, dan tren awal sebelum analisis mendalam.</p>
                </div>
                <div class="col-md-3 text-md-end">
                    <a href="{{ route('risk.dashboard') }}" class="btn btn-outline-primary">Lihat Overview</a>
                </div>
            </div>
        </div>
    </div>

    <!-- STEP 4 - ANALISIS -->
    <div class="col-md-12">
        <div class="card step-card p-3">
            <div class="row align-items-center">
                <div class="col-md-1 text-center">
                    <div class="step-number">4</div>
                </div>
                <div class="col-md-8">
                    <h5 class="d-flex align-items-center">
                        <i class="bi bi-graph-up step-icon"></i>
                        Analisis & Visualisasi
                    </h5>
                    <p class="text-muted">
                        Lihat analisis Monte Carlo, VaR, CVaR, korelasi, dan grafik interaktif berbasis data.
                    </p>
                </div>
                <div class="col-md-3 text-md-end">
                    <a href="{{ route('risk.analysis') }}" class="btn btn-outline-primary">Masuk ke Analisis</a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
