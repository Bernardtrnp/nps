@extends('layouts.app')

@section('content')
<div class="container" x-data="riskAnalysis()" x-init="init()">

    <h2 class="mb-4 fw-bold">
        📈 Advanced Risk Analysis Dashboard
    </h2>

    {{-- SELECT RISK TYPE --}}
    <div class="card shadow-sm p-3 mb-4">
        <label class="fw-bold mb-2">Pilih Risk Type</label>
        <select class="form-select" x-model="selectedRiskType" @change="updateRiskType">
            <option value="">-- pilih risk type --</option>
            @foreach($riskTypes as $rt)
                <option value="{{ $rt->id }}">{{ $rt->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- SUMMARY + INSIGHT --}}
    <template x-if="current">
        <div>
            <div class="card shadow-sm p-4 mb-4 text-center"
                 style="border-left: 6px solid #0d6efd;">

                <h3 class="fw-bold" x-text="current.risk_type_name"></h3>

                <div class="mt-3 row justify-content-center">

                    <template x-for="(val, key) in current.summary">
                        <div class="col-md-2">
                            <div class="p-3 rounded shadow-sm bg-white">
                                <div class="small text-muted text-uppercase" x-text="key"></div>
                                <div class="fw-bold fs-4" x-text="val"></div>
                            </div>
                        </div>
                    </template>

                </div>

                {{-- AI INSIGHT --}}
                <div class="alert alert-primary mt-4">
                    <h5 class="fw-bold">💡 Insight AI Risiko</h5>
                    <div x-text="current.insight_ai"></div>
                </div>

                {{-- RECOMMENDATIONS --}}
                <div class="alert alert-warning mt-3">
                    <h5 class="fw-bold">🏳️ Rekomendasi Otomatis</h5>
                    <ul class="mb-0">
                        <template x-for="r in current.recommendations">
                            <li x-text="r"></li>
                        </template>
                    </ul>
                </div>

            </div>

            {{-- TAB SELECTOR --}}
            <ul class="nav nav-tabs mb-4">
                <template x-for="mode in ['unit','entitas','category']">
                    <li class="nav-item">
                        <button class="nav-link"
                            :class="tab === mode ? 'active' : ''"
                            @click="tab=mode; renderChart()">
                            <span x-text="'Per ' + mode.charAt(0).toUpperCase() + mode.slice(1)"></span>
                        </button>
                    </li>
                </template>
            </ul>

            {{-- CHART --}}
            <div class="card shadow-sm p-3">
                <canvas id="riskChart" height="80"></canvas>
            </div>

        </div>
    </template>

</div>
@endsection


@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
function riskAnalysis(){
    return {
        selectedRiskType: '',
        allPayload: @json($payload),
        current: null,
        tab: 'unit',
        chart: null,

        init(){
            console.log("Payload loaded:", this.allPayload);
        },

        updateRiskType(){
            this.current = this.allPayload[this.selectedRiskType] ?? null;

            if(this.current){
                this.tab = this.current.default_tab ?? 'unit';
                this.$nextTick(() => this.renderChart());
            }
        },

        getChartData(){
            if(!this.current) return null;
            return this.current['per_' + this.tab];
        },

        renderChart(){
            let group = this.getChartData();
            if(!group) return;

            let ctx = document.getElementById('riskChart').getContext('2d');

            if(this.chart){
                this.chart.destroy();
            }

            this.chart = new Chart(ctx, {
                type: "bar",
                data: {
                    labels: group.labels ?? [],
                    datasets: group.datasets.map(ds => ({
                        label: ds.label,
                        data: ds.data,
                        stack: ds.stack ?? null,
                        backgroundColor: this.randomColor(ds.label)
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
        },

        randomColor(key){
            let hash = 0;
            for (let i = 0; i < key.length; i++){
                hash = key.charCodeAt(i) + ((hash << 5) - hash);
            }
            let color = "#";
            for (let i = 0; i < 3; i++){
                let v = (hash >> (i * 8)) & 255;
                color += ("00" + v.toString(16)).substr(-2);
            }
            return color + "88";
        }
    }
}
</script>
@endsection
