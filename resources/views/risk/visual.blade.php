@extends('layouts.app')

@section('intro')
<div class="mb-3">
    <p>Halaman ini menampilkan grafik Monte Carlo dan heatmap korelasi antar subkategori risiko. Cocok untuk eksplorasi visual dan presentasi.</p>
</div>
@endsection

@section('content')
<div class="container">
    <h2 class="mb-4">📊 Visualisasi Risiko Tahun {{ $year }} - {{ $riskType }}</h2>

    <!-- 🔍 Filter Dinamis -->
    <form method="GET" action="{{ route('risk.analysis') }}" class="row g-3 mb-4">
        <div class="col-md-3">
            <label for="year" class="form-label">Tahun</label>
            <select name="year" id="year" class="form-select">
                @foreach($availableYears as $y)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label for="risk_type" class="form-label">Jenis Risiko</label>
            <select name="risk_type" id="risk_type" class="form-select">
                @foreach($availableTypes as $type)
                    <option value="{{ $type }}" {{ $type == $riskType ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3 align-self-end">
            <button type="submit" class="btn btn-primary">🔍 Tampilkan</button>
        </div>
    </form>

    <!-- 📈 Monte Carlo Chart -->
    @if(isset($monteCarlo) && count($monteCarlo))
    <h5 class="mt-4">Simulasi Monte Carlo: {{ $selectedSubcategory }}</h5>
    <canvas id="monteCarloChart" width="600" height="300"></canvas>
    @endif

    <!-- 🔥 Heatmap Korelasi -->
    @if(isset($correlationMatrix) && count($correlationMatrix))
    <h5 class="mt-5">Heatmap Korelasi Subkategori</h5>
    <canvas id="correlationHeatmap" width="600" height="300"></canvas>
    @endif
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@if(isset($monteCarlo))
<script>
const monteCarloData = @json($monteCarlo);
const ctx1 = document.getElementById('monteCarloChart').getContext('2d');
new Chart(ctx1, {
    type: 'bar',
    data: {
        labels: monteCarloData.map((_, i) => i + 1),
        datasets: [{
            label: 'Simulasi Monte Carlo',
            data: monteCarloData,
            backgroundColor: 'rgba(54, 162, 235, 0.6)',
        }]
    },
    options: {
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>
@endif

@if(isset($correlationMatrix))
<script>
const correlationMatrix = @json($correlationMatrix);
const labels = Object.keys(correlationMatrix);
const data = {
    labels: labels,
    datasets: labels.map(row => ({
        label: row,
        data: labels.map(col => correlationMatrix[row][col]),
        backgroundColor: labels.map(col => {
            const v = correlationMatrix[row][col];
            const r = Math.round(255 - v * 255);
            const g = Math.round(v * 255);
            return `rgba(${r}, ${g}, 150, 0.8)`;
        }),
    }))
};

const ctx2 = document.getElementById('correlationHeatmap').getContext('2d');
new Chart(ctx2, {
    type: 'bar', // fallback jika plugin heatmap tidak tersedia
    data: data,
    options: {
        indexAxis: 'y',
        plugins: {
            legend: { display: false }
        },
        scales: {
            x: { beginAtZero: true, max: 1 },
            y: { stacked: true }
        }
    }
});
</script>
@endif
@endsection
