@extends('layouts.app')

@section('content')

<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill,minmax(600px,1fr));
        gap: 25px;
    }
    .panel {
        border-radius: 12px;
        padding: 18px;
        background: #ffffff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }
    .summary-box {
        padding: 10px 18px;
        border-radius: 12px;
        background: #f7f9fc;
        box-shadow: inset 0 0 6px rgba(0,0,0,0.05);
        text-align: center;
        min-width: 110px;
    }
    .summary-box small {
        font-size: 12px;
        color: #6b7280;
        font-weight: bold;
    }
    .summary-box div {
        font-size: 22px;
        font-weight: 700;
        margin-top: 2px;
    }
</style>

<div class="container">

    <h2 class="mb-4 fw-bold">📊 Advanced Risk Dashboard</h2>

    {{-- FILTER FORM --}}
    <form method="GET" class="card p-3 shadow-sm mb-4">
        <label class="fw-bold mb-2">Pilih Risk Type</label>

        <select name="risk_type[]" multiple class="form-select" style="height: 140px;">
            @foreach($riskTypes as $rt)
                <option value="{{ $rt->id }}" 
                    @if(in_array($rt->id, $selectedRiskTypes)) selected @endif>
                    {{ $rt->name }}
                </option>
            @endforeach
        </select>

        <button class="btn btn-primary mt-3">Apply</button>
    </form>

    {{-- MAIN GRID --}}
    <div class="dashboard-grid">

        @forelse($payload as $panel)
            <div class="panel">

                <h4 class="fw-bold mb-3">{{ $panel['risk_type_name'] }}</h4>

                {{-- SUMMARY --}}
                <div class="d-flex justify-content-center gap-3 mb-4">

                    <div class="summary-box">
                        <small>SUM</small>
                        <div>{{ $panel['summary']['sum'] }}</div>
                    </div>

                    <div class="summary-box">
                        <small>AVG</small>
                        <div>{{ $panel['summary']['avg'] }}</div>
                    </div>

                    <div class="summary-box">
                        <small>MIN</small>
                        <div>{{ $panel['summary']['min'] }}</div>
                    </div>

                    <div class="summary-box">
                        <small>MAX</small>
                        <div>{{ $panel['summary']['max'] }}</div>
                    </div>

                </div>

                {{-- TABS --}}
                <ul class="nav nav-tabs mb-3" id="tab-{{ $panel['risk_type_name'] }}">
                    <li class="nav-item">
                        <a class="nav-link active" data-type="unit" href="#">Per Unit</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-type="entitas" href="#">Per Entitas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-type="category" href="#">Per Subkategori</a>
                    </li>
                </ul>

                {{-- CHART --}}
                <canvas id="chart-{{ $panel['risk_type_name'] }}" height="130"></canvas>

            </div>
        @empty
            <p class="text-muted">Tidak ada data.</p>
        @endforelse

    </div>

</div>
@endsection


@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {

    let payload = @json($payload);

    Object.keys(payload).forEach(typeId => {
        let panel = payload[typeId];
        let name = panel.risk_type_name;

        let ctx = document.getElementById(`chart-${name}`).getContext('2d');

        let defaultTab = panel.default_tab;

        let chartRef = null;

        function render(type){
            let config = panel[`per_${type}`];
            if(!config) return;

            if(chartRef) chartRef.destroy();

            chartRef = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: config.labels,
                    datasets: config.datasets.map(ds => ({
                        label: ds.label,
                        data: ds.data,
                        backgroundColor: dynamicColor(ds.label),
                        stack: type // allow stacking
                    }))
                },
                options: {
                    responsive: true,
                    scales: {
                        x: { stacked: true },
                        y: { stacked: true, beginAtZero: true }
                    }
                }
            });
        }

        function dynamicColor(key){
            let h = 0;
            for (let i = 0; i < key.length; i++)
                h = key.charCodeAt(i) + ((h << 5) - h);
            return `hsl(${h % 360},70%,65%)`;
        }

        // default load
        render(defaultTab);

        // tab events
        let tabs = document.querySelectorAll(`#tab-${name} .nav-link`);

        tabs.forEach(t => {
            t.addEventListener('click', e => {
                e.preventDefault();
                tabs.forEach(x => x.classList.remove('active'));
                t.classList.add('active');
                render(t.dataset.type);
            });
        });

    });

});
</script>
@endsection
