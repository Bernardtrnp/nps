@extends('layouts.app')

@section('content')
<div class="container mb-5" style="max-width: 1050px;">

    <h2 class="mb-4 fw-bold">
        <i class="bi bi-graph-up-arrow"></i> Risk Summary — CVaR Based Ranking
    </h2>

    {{-- TOP 3 CARDS --}}
    @if(count($ranking) > 0)
        <div class="row mb-4">
            @foreach(array_slice($ranking, 0, 3) as $i => $r)
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 mb-3" style="border-left:6px solid #dc3545;">
                        <div class="card-body">
                            <h6 class="text-muted mb-1">#{{ $i+1 }} Highest Risk</h6>
                            <h5 class="fw-bold">{{ $r['risk_name'] }}</h5>
                            <div class="small text-muted">{{ $r['risk_type'] }}</div>

                            <div class="mt-2">
                                <div><strong>CVaR:</strong> {{ $r['cvar'] }}</div>
                                <div><strong>Latest:</strong> {{ $r['latest'] }}</div>
                                <div>
                                    <strong>Trend:</strong>
                                    <span class="{{ $r['trend'] >= 0 ? 'text-danger' : 'text-success' }}">
                                        {{ $r['trend'] >= 0 ? '+' : '' }}{{ $r['trend'] }}
                                    </span>
                                </div>
                            </div>

                            <hr class="my-2">

                            <div class="small text-muted">
                                Unit/Entitas:
                                <strong>{{ $r['unit'] ?? $r['entitas'] ?? '-' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif


    {{-- FULL RANKING TABLE --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="fw-bold mb-3">
                <i class="bi bi-list-ol"></i> Full Risk Ranking (CVaR — Worst 5%)
            </h5>

            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Rank</th>
                            <th>Risk Name</th>
                            <th>Risk Type</th>
                            <th>Unit / Entitas</th>
                            <th>CVaR (5%)</th>
                            <th>Latest</th>
                            <th>Trend</th>
                            <th>Data Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ranking as $i => $r)
                            <tr>
                                <td class="fw-bold">{{ $i+1 }}</td>
                                <td>{{ $r['risk_name'] }}</td>
                                <td>{{ $r['risk_type'] }}</td>
                                <td>{{ $r['unit'] ?? $r['entitas'] ?? '-' }}</td>
                                <td class="text-danger fw-bold">{{ $r['cvar'] }}</td>
                                <td>{{ $r['latest'] }}</td>
                                <td class="{{ $r['trend'] >= 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $r['trend'] >= 0 ? '+' : '' }}{{ $r['trend'] }}
                                </td>
                                <td>{{ $r['count'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>


    {{-- DISCLAIMER --}}
    <div class="alert alert-info mt-4" style="border-left: 5px solid #0d6efd;">
        <h6 class="fw-bold"><i class="bi bi-info-circle"></i> Disclaimer Analisis Statistik</h6>
        <p class="mb-0">
            Ranking risiko ini dihitung menggunakan metode
            <strong>CVaR (Conditional Value at Risk / Expected Shortfall)</strong>, 
            yaitu rata-rata dari <strong>5% nilai terburuk</strong> pada data historis.
            <br><br>
            Hasil ini <strong>bukan penetapan Top Risk final</strong>, melainkan
            <strong>indikator statistik</strong>.  
            Validasi tetap diperlukan oleh pemilik risiko dan tim terkait.
        </p>
    </div>

</div>
@endsection
