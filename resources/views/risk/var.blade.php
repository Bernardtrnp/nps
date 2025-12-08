@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">📉 Analisis Risiko - VaR & Distribusi</h2>

    <!-- 🔍 Form Analisis -->
    <form method="GET" action="{{ route('risk.var') }}" class="row align-items-end g-3 mb-4">
        <!-- Mode Analisis -->
        <div class="col-md-3">
            <label for="mode" class="form-label">Mode Analisis</label>
            <select name="mode" class="form-select" onchange="this.form.submit()">
                <option value="jenis" {{ $mode == 'jenis' ? 'selected' : '' }}>Per Jenis Risiko</option>
                <option value="subkategori" {{ $mode == 'subkategori' ? 'selected' : '' }}>Per Subkategori Risiko</option>
            </select>
        </div>

        <!-- Jenis/Subkategori -->
        @if($mode === 'jenis')
        <div class="col-md-3">
            <label for="risk_type" class="form-label">Jenis Risiko</label>
            <select name="risk_type" class="form-select">
                @foreach($availableTypes as $type)
                    <option value="{{ $type }}" {{ $type == $selectedType ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
        </div>
        @else
        <div class="col-md-3">
            <label for="subcategory" class="form-label">Subkategori Risiko</label>
            <select name="subcategory" class="form-select">
                @foreach($availableSubcategories as $sub)
                    <option value="{{ $sub }}" {{ $sub == $selectedSubcategory ? 'selected' : '' }}>{{ $sub }}</option>
                @endforeach
            </select>
        </div>
        @endif

        <!-- Confidence Level -->
        <div class="col-md-3">
            <label for="confidence" class="form-label">Confidence Level</label>
            <div class="d-flex align-items-center gap-3">
                <input type="range" name="confidence" id="confidence" class="form-range flex-grow-1" min="90" max="99" value="{{ $confidence }}" oninput="document.getElementById('confLabel').innerText = this.value + '%'">
                <span class="fw-bold" id="confLabel">{{ $confidence }}%</span>
            </div>
        </div>

        <!-- Tombol Analisis -->
        <div class="col-md-3">
            <label class="form-label d-block">&nbsp;</label>
            <button type="submit" class="btn btn-primary w-100">🔍 Analisis</button>
        </div>
        
        <div class="d-flex justify-content-end mb-3 gap-2">
            <a href="{{ route('risk.var.export', request()->query()) }}" class="btn btn-outline-secondary">
                📄 Export PDF
            </a>
            <button id="downloadChart" class="btn btn-outline-info">
                🖼️ Simpan Snapshot Chart
            </button>
        </div>

    </form>

    <!-- 🧠 Rekomendasi Metode -->
    <div class="alert alert-info">
        <strong>🧠 Rekomendasi Metode:</strong> {!! $methodExplanation !!}
    </div>

    <!-- 📈 Chart Distribusi -->
    <canvas id="distributionChart" width="600" height="300"></canvas>

    <!-- 📋 Tabel Hasil -->
    <table class="table table-bordered mt-4">
        <thead class="table-light">
            <tr>
                <th>Confidence Level</th>
                <th>Parametric VaR</th>
                <th>Historical VaR</th>
                <th>Monte Carlo VaR</th>
                <th>CVaR</th>
                <th>Mean</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $confidence }}%</td>
                <td>{{ number_format($parametricVaR, 4) }}</td>
                <td>{{ number_format($historicalVaR, 4) }}</td>
                <td>{{ number_format($monteCarloVaR, 4) }}</td>
                <td>{{ number_format($cvar, 4) }}</td>
                <td>{{ number_format($mean, 4) }}</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@1.1.0"></script>
<script>
const ctx = document.getElementById('distributionChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: @json($distributionLabels),
        datasets: [{
            label: 'Distribusi Risiko',
            data: @json($distributionValues),
            backgroundColor: 'rgba(52, 152, 219, 0.6)',
        }]
    },
    options: {
        plugins: {
            annotation: {
                annotations: {
                    varLine: {
                        type: 'line',
                        borderColor: 'red',
                        borderDash: [6, 6],
                        borderWidth: 2,
                        label: { content: 'VaR', enabled: true },
                        value: {{ $parametricVaR }},
                        scaleID: 'x',
                    },
                    cvarLine: {
                        type: 'line',
                        borderColor: 'blue',
                        borderDash: [6, 6],
                        borderWidth: 2,
                        label: { content: 'CVaR', enabled: true },
                        value: {{ $cvar }},
                        scaleID: 'x',
                    },
                    meanLine: {
                        type: 'line',
                        borderColor: 'purple',
                        borderDash: [6, 6],
                        borderWidth: 2,
                        label: { content: 'Mean', enabled: true },
                        value: {{ $mean }},
                        scaleID: 'x',
                    }
                }
            }
        },
        scales: {
            x: { title: { display: true, text: 'Nilai Risiko' } },
            y: { beginAtZero: true, title: { display: true, text: 'Frekuensi' } }
        }
    }
});
</script>
<script>
document.getElementById('downloadChart').addEventListener('click', function () {
    const canvas = document.getElementById('distributionChart');
    const link = document.createElement('a');
    link.download = 'risk_distribution_chart.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
});
</script>

@endsection
