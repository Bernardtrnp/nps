@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <div x-data="statAnalysisEcharts()" x-init="init()" class="w-100">
        <h2 class="mb-4 fw-bold">
            <i class="bi bi-bar-chart-line-fill me-2"></i>
            Statistical Analysis Center — Histogram + PDF Curve
        </h2>

        {{-- Risk Type selector --}}
        <div class="card shadow-sm mb-4 p-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <h5 class="fw-bold mb-1">Pilih Jenis Risiko</h5>
                    <select x-model="riskTypeSelected" class="form-select mt-2">
                        <option value="">-- Semua Risk Type --</option>
                        @foreach($riskTypes as $rt)
                            <option value="{{ $rt->id }}">{{ $rt->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 text-end">
                    <div class="form-text small text-muted">Tip: jika suatu variable muncul lebih dari sekali (karena multi-unit), gunakan opsi *Group (Aggregate)* untuk analisis gabungan.</div>
                </div>
            </div>
        </div>

        {{-- Controls --}}
        <div class="card shadow-sm p-3 mb-3">
            <div class="row mb-3">
                <div class="col-md-5">
                    <label class="form-label">Pilih Variable (Specific)</label>
                    <select x-model="selectedVariableId" class="form-select" @change="onVariableChange">
                        <option value="">-- Pilih Variabel Spesifik --</option>
                        <template x-for="v in filteredVariables" :key="v.label">
                            <!-- value is the label (unique UI key); actual member ids are in __merged_ids -->
                            <option :value="v.label" x-text="v.label"></option>
                        </template>
                    </select>
                    <div class="form-text small text-muted mt-1">Pilih variable per-unit / per-entitas jika ingin modelling skala unit/entitas.</div>
                </div>

                <div class="col-md-5">
                    <label class="form-label">Pilih Variable (Group / Aggregate)</label>
                    <select x-model="selectedGroupId" class="form-select" @change="onGroupChange">
                        <option value="">-- Pilih Variabel untuk Aggregate --</option>
                        <template x-for="g in filteredGroups" :key="g.group_id">
                            <option :value="g.group_id" x-text="buildGroupLabel(g)"></option>
                        </template>
                    </select>
                    <div class="form-text small text-muted mt-1">Gunakan ini jika variable tersedia di beberapa unit/entitas dan Anda ingin modeling gabungan.</div>
                </div>

                <div class="col-md-2">
                    <label class="form-label" title="Jumlah simulasi Monte Carlo yang dijalankan untuk membentuk estimasi distribusi risiko. Semakin banyak simulasi, hasil makin stabil.">
                        Simulations: <span x-text="simN"></span> <span style="cursor:pointer;">ⓘ</span>
                    </label>               
                    <input type="range" min="1000" max="50000" step="1000" x-model.number="simN" class="form-range" @input="runSimulationDebounced">
                </div>
            </div>

            <div class="row mb-3 align-items-center">
                <div class="col-md-8">
                    <label class="form-label" title="Tingkat keyakinan statistik untuk VaR dan CVaR.">
                        Confidence: <span x-text="(confidence*100).toFixed(0) + '%'"></span> <span style="cursor:pointer;">ⓘ</span>
                    </label>
                    <input type="range" min="0.5" max="0.999" step="0.01" x-model.number="confidence" class="form-range" @input="runSimulationDebounced">
                </div>

                <div class="col-md-4 text-end">
                    <button class="btn btn-outline-secondary btn-sm" @click="clearSelection">Clear</button>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded">
                        <div class="small text-muted">
                            Distribution <span style="cursor:pointer;">ⓘ</span>
                        </div>
                        <div class="h4 fw-bold" x-text="distInfo.name ? distInfo.name.toUpperCase() : 'UNKNOWN'"></div>
                        <div class="small text-muted" x-text="distInfo.reason"></div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 bg-light rounded">
                        <div class="small text-muted">
                            VaR (<span x-text="(confidence*100).toFixed(0)"></span>%) <span style="cursor:pointer;">ⓘ</span>
                        </div>
                        <div class="h4 fw-bold" x-text="varValue!==null ? varValue.toFixed(2) : '-'"></div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 bg-light rounded">
                        <div class="small text-muted">
                            CVaR <span style="cursor:pointer;">ⓘ</span>
                        </div>                        
                        <div class="h4 fw-bold" x-text="cvarValue!==null ? cvarValue.toFixed(2) : '-'"></div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Chart + Detail --}}
        <div class="row">
            <div class="col-md-8">
                <div class="p-3 bg-white rounded shadow-sm" style="height:420px;">
                    <h5 title="Histogram menunjukkan frekuensi nilai hasil simulasi. PDF Curve menunjukkan bentuk distribusi yang dihaluskan untuk melihat pola risiko secara visual.">
                        Histogram + PDF Curve <span style="cursor:pointer;">ⓘ</span>
                    </h5>
                    <div id="echarts-root" style="height:360px; width:100%;"></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="p-3 bg-white rounded shadow-sm">
                    <h5>Detail Variabel</h5>
                    <div class="small"><strong>Selected:</strong> <span x-text="selectedLabel || '-'"></span></div>
                    <div class="small"><strong>Count:</strong> <span x-text="currentCount"></span></div>
                    <div class="small"><strong>Mean:</strong> <span x-text="currentMean!==null?currentMean.toFixed(2):'-'"></span></div>
                    <div class="small"><strong>Std Dev:</strong> <span x-text="currentStd!==null?currentStd.toFixed(2):'-'"></span></div>
                    <div class="small"><strong>Value type:</strong> <span x-text="currentValueType||'-'"></span></div>
                    <div class="small"><strong>Time dimension:</strong> <span x-text="currentTimeDimension||'-'"></span></div>
                    <div class="small" title="Persentase kelengkapan data berdasarkan periode waktu.">
                        <strong>Completeness:</strong> <span x-text="currentCompleteness||'-'"></span>% <span style="cursor:pointer;">ⓘ</span>
                    </div>                
                    <div class="small"><strong>Last updated:</strong> <span x-text="currentLastUpdated||'-'"></span></div>
                    <hr>
                    <button class="btn btn-outline-success btn-sm w-100 mt-2" @click="downloadPNG" :disabled="!chart">
                        Download Chart (PNG)
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- server data (variables + groups) --}}
<div id="server-data" style="display:none"
     data-variables='@json($variables)'
     data-groups='@json($variable_groups)'></div>
@endsection

@section('scripts')
<!-- Alpine -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<!-- ECharts -->
<script src="https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js"></script>

<script>
function statAnalysisEcharts(){
    return {
        // server
        rawVariables: [],
        rawGroups: [],

        // selection
        riskTypeSelected: '',
        selectedVariableId: null,
        selectedGroupId: null,

        // sim params
        simN: 5000,
        confidence: 0.95,

        // stats / samples
        distInfo: { name: null, params: {}, reason: '' },
        samples: [],
        varValue: null,
        cvarValue: null,

        // metadata
        selectedLabel: '',
        currentCount: 0,
        currentMean: null,
        currentStd: null,
        currentValueType: null,
        currentTimeDimension: null,
        currentCompleteness: null,
        currentLastUpdated: null,

        // echarts instance
        chart: null,
        echartsRoot: null,
        initOnce: false,

        init(){
            if (this.initOnce) return;
            this.initOnce = true;

            try {
                const el = document.getElementById('server-data');
                this.rawVariables = el ? JSON.parse(el.getAttribute('data-variables')) : [];
                this.rawGroups = el ? JSON.parse(el.getAttribute('data-groups')) : [];
            } catch(e){
                console.error('Invalid server-data', e);
                this.rawVariables = [];
                this.rawGroups = [];
            }

            this.echartsRoot = document.getElementById('echarts-root');

            try {
                this.chart = echarts.init(this.echartsRoot, null, { renderer: 'svg' });
            } catch(err){
                // fallback
                this.chart = echarts.init(this.echartsRoot);
            }

            // initial empty option
            this.chart.setOption({
                title: { text: '' },
                tooltip: { trigger: 'axis', axisPointer: { type: 'cross' } },
                legend: { data: ['Histogram','PDF'], top: 6 },
                grid: { left: '6%', right: '6%', bottom: '8%', top:'12%' },
                xAxis: [{ type:'value', name: 'Value' }],
                yAxis: [{ type:'value', name: 'Count' }],
                series: [
                    { name:'Histogram', type:'bar', data:[], itemStyle:{color:'#7FB3FF'} },
                    { name:'PDF', type:'line', data:[], smooth:true, showSymbol:false, lineStyle:{width:2, color:'#1F77B4'} }
                ]
            });

            window.addEventListener('resize', () => {
                if (this.chart) this.chart.resize();
            });
        },

        /* ================== FILTERED & DEDUPE (merge by UI label) ================== */

        // build specific UI label (unit/entitas/risk_name fallback)
        buildSpecificLabelFromRaw(v){
            // Unit -> include risk_name to avoid collapsing different risks that share same unit
            if (v.unit_name) {
                return `Unit — ${v.unit_name} — ${v.risk_name}`;
            }
            // Entitas -> include risk_name
            if (v.entitas_name) {
                return `Entitas — ${v.entitas_name} — ${v.risk_name}`;
            }
            // If no unit/entitas, use risk_name — variable_name (this is most specific)
            return `${v.risk_name} — ${v.variable_name}`;
        },

        // filteredVariables: returns merged UI entries (label + __merged_ids)
        get filteredVariables(){
            if(!this.riskTypeSelected) return [];

            // keep all raw entries for selected risk type
            const vars = this.rawVariables.filter(v => String(v.risk_type_id) === String(this.riskTypeSelected));

            // map by UI label (merge members with identical UI label)
            const map = {};
            vars.forEach(v => {
                const label = this.buildSpecificLabelFromRaw(v);

                if (!map[label]) {
                    map[label] = {
                        label: label,
                        // metadata: choose representative fields (we'll compute aggregated values later)
                        risk_type_id: v.risk_type_id,
                        // keep arrays of metadata in case group needs them
                        value_types: v.value_type ? [v.value_type] : [],
                        time_dimensions: v.time_dimension ? [v.time_dimension] : [],
                        completeness_list: v.completeness ? [v.completeness] : [],
                        last_updated_list: v.last_updated ? [v.last_updated] : [],
                        __merged_ids: [v.risk_variable_id]
                    };
                } else {
                    // append metadata lists
                    if (v.value_type && !map[label].value_types.includes(v.value_type)) map[label].value_types.push(v.value_type);
                    if (v.time_dimension && !map[label].time_dimensions.includes(v.time_dimension)) map[label].time_dimensions.push(v.time_dimension);
                    if (v.completeness) map[label].completeness_list.push(v.completeness);
                    if (v.last_updated) map[label].last_updated_list.push(v.last_updated);
                    map[label].__merged_ids.push(v.risk_variable_id);
                }
            });

            // convert to array
            return Object.values(map);
        },

        // filteredGroups unchanged: groups are prepared server-side
        get filteredGroups(){
            if(!this.riskTypeSelected) return [];
            return this.rawGroups.filter(g => String(g.risk_type_id) === String(this.riskTypeSelected));
        },

        // group label builder unchanged (uses g.label from server)
        buildGroupLabel(g){
            let mainLabel = g.label; // already category or fallback risk_name

            const unitHint = (g.available_units && g.available_units.length)
                ? `Units: ${g.available_units.join(', ')}`
                : '';

            const entHint = (g.available_entitas && g.available_entitas.length)
                ? `Entitas: ${g.available_entitas.join(', ')}`
                : '';

            const hint = unitHint || entHint ? ` (${unitHint || entHint})` : '';

            return `${mainLabel} — GROUP${hint}`;
        },

        /* ================== Actions ================== */

        clearSelection(){
            this.selectedVariableId = null;
            this.selectedGroupId = null;
            this.reset();
        },

        // onGroupChange (aggregate across group members) — unchanged logic
        onGroupChange(){
            this.selectedVariableId = null;

            const g = this.rawGroups.find(x => x.group_id === this.selectedGroupId);
            if(!g){ this.reset(); return; }

            // aggregate values from all members (per period sum)
            const periodMap = {}; // key -> { sum, count }
            g.members.forEach(m => {
                (m.values || []).forEach(r => {
                    const key = this._periodKey(r);
                    const val = Number(r.value);
                    if(isNaN(val)) return;
                    if(!periodMap[key]) periodMap[key] = { sum: 0, count: 0 };
                    periodMap[key].sum += val;
                    periodMap[key].count += 1;
                });
            });

            // aggregated numeric array (we use summed-per-period values)
            const aggregated = Object.keys(periodMap).map(k => periodMap[k].sum);

            this.selectedLabel = `${g.label} — GROUP (${g.members.length} members)`;            
            this.currentCount = aggregated.length;
            this.currentMean = aggregated.length ? mean(aggregated) : null;
            this.currentStd = aggregated.length>1 ? std(aggregated) : null;
            this.currentValueType = (g.value_types && g.value_types.length) ? g.value_types[0] : null;
            this.currentTimeDimension = (g.time_dimensions && g.time_dimensions.length) ? g.time_dimensions[0] : null;
            this.currentCompleteness = null;
            this.currentLastUpdated = null;

            this.distInfo = detectDistribution(aggregated, this.currentValueType || 'auto');
            this.samples = runMonteCarlo(this.distInfo, this.simN);
            this.varValue = quantile(this.samples, this.confidence);
            this.cvarValue = cvar(this.samples, this.confidence);

            this.renderChartFromSamples(this.samples);
        },

        // onVariableChange: handle merged ids and aggregate by period (sum) before stats
        onVariableChange(){
            this.selectedGroupId = null;

            if(!this.selectedVariableId){
                this.reset(); return;
            }

            // find merged entry in filteredVariables by label
            const merged = this.filteredVariables.find(x => x.label === this.selectedVariableId);
            if(!merged){ this.reset(); return; }

            // build period map by summing values for identical periods across all merged members
            const periodMap = {}; // key -> { sum, count, last_updated_list[] }
            merged.__merged_ids.forEach(id => {
                // find raw variable by id in rawVariables
                const raw = this.rawVariables.find(r => Number(r.risk_variable_id) === Number(id));
                if(!raw || !raw.values) return;
                (raw.values || []).forEach(entry => {
                    const key = this._periodKey(entry); // e.g., 2024-05 or 2024-Q1 or 2024
                    const val = Number(entry.value);
                    if(isNaN(val)) return;
                    if(!periodMap[key]) periodMap[key] = { sum: 0, count: 0 };
                    periodMap[key].sum += val;
                    periodMap[key].count += 1;
                });
            });

            // convert periodMap into numeric array for stats (we use summed values per period)
            const aggregated = Object.keys(periodMap)
                .sort() // optional chronological ordering by key strings (YYYY... works)
                .map(k => periodMap[k].sum);

            // metadata
            this.selectedLabel = merged.label;
            this.currentCount = aggregated.length;
            this.currentMean = aggregated.length ? mean(aggregated) : null;
            this.currentStd = aggregated.length>1 ? std(aggregated) : null;
            // choose first available value_type / time_dimension if multiple exist
            this.currentValueType = merged.value_types && merged.value_types.length ? merged.value_types[0] : null;
            this.currentTimeDimension = merged.time_dimensions && merged.time_dimensions.length ? merged.time_dimensions[0] : null;
            // completeness = average completeness if present
            this.currentCompleteness = merged.completeness_list && merged.completeness_list.length
                ? Math.round((merged.completeness_list.reduce((s,n)=>s+Number(n),0) / merged.completeness_list.length) * 10) / 10
                : null;
            this.currentLastUpdated = merged.last_updated_list && merged.last_updated_list.length
                ? merged.last_updated_list.sort().reverse()[0]
                : null;

            // distribution + MC
            this.distInfo = detectDistribution(aggregated, this.currentValueType || 'auto');
            this.samples = runMonteCarlo(this.distInfo, this.simN);
            this.varValue = quantile(this.samples, this.confidence);
            this.cvarValue = cvar(this.samples, this.confidence);

            this.renderChartFromSamples(this.samples);
        },

        /* small helper to build a normalized period key for aggregation */
        _periodKey(entry){
            // entry: { value, year, month, quarter }
            if(entry.month) {
                // normalize month string to number if possible, otherwise use raw month
                // prefer format YYYY-MM (month numeric if month already numeric)
                const m = String(entry.month).padStart(2,'0');
                return `${entry.year}-${m}`;
            }
            if(entry.quarter) {
                // quarter may be 'Q1' or number; normalize to Q#
                const q = String(entry.quarter).replace(/^Q/i,'');
                return `${entry.year}-Q${q}`;
            }
            return String(entry.year);
        },

        /* ================== Chart & util functions (unchanged) ================== */

        renderChartFromSamples(samples){
            const bins = 40;
            const hist = histogramBuckets(samples, bins);
            const grid = kdeGrid(samples, 256);
            const binWidth = (hist.length>0) ? (hist[0].end - hist[0].start || 1) : 1;
            const kdeY = grid.density.map(d => d * samples.length * binWidth);
            const xVals = grid.x;
            const histXCenters = hist.map(h => (h.start + h.end) / 2);
            const histData = hist.map(h => h.count);

            this.chart.setOption({
                tooltip: { trigger: 'axis', formatter: (params) => {
                    let out = '';
                    params.forEach(p => {
                        if (p.seriesName === 'Histogram') {
                            out += `<div>${p.seriesName}: ${p.value}</div>`;
                        } else if (p.seriesName === 'PDF') {
                            out += `<div>${p.seriesName} (${p.axisValue.toFixed(2)}): ${p.value.toFixed(3)}</div>`;
                        }
                    });
                    return out;
                } },
                xAxis: [{ type:'value', name: 'Value' }],
                yAxis: [{ type:'value', name: 'Count' }],
                series: [
                    {
                        name: 'Histogram',
                        type: 'bar',
                        data: histXCenters.map((xc, i) => [xc, histData[i]]),
                        barWidth: (hist.length ? (hist[0].end - hist[0].start) : undefined),
                        itemStyle:{color:'#7FB3FF'}
                    },
                    {
                        name: 'PDF',
                        type: 'line',
                        data: xVals.map((x,i) => [x, kdeY[i]]),
                        smooth: true,
                        showSymbol: false,
                        lineStyle:{width:2, color:'#1F77B4'}
                    }
                ]
            }, { replaceMerge: ['series','xAxis','yAxis','tooltip'] });

            this.chart.resize();
        },

        reset(){
            this.selectedLabel = '';
            this.currentCount = 0;
            this.currentMean = null;
            this.currentStd = null;
            this.currentValueType = this.currentTimeDimension = this.currentCompleteness = this.currentLastUpdated = null;
            this.samples = [];
            this.varValue = this.cvarValue = null;
            // reset chart to empty
            this.chart.setOption({
                xAxis: [{ type: 'value', data: [] }],
                series: [
                    { name:'Histogram', data: [] },
                    { name:'PDF', data: [] }
                ]
            });
        },

        downloadPNG(){
            if (!this.chart) return;

            const url = this.chart.getDataURL({
                type: 'png',
                pixelRatio: 2,
                backgroundColor: '#ffffff'
            });

            const a = document.createElement('a');
            a.href = url;

            const name = this.selectedLabel
                ? this.selectedLabel.replace(/\s+/g, '_')
                : 'chart';

            a.download = `${name}.png`;
            a.click();
        },

        runSimulationDebounced: debounce(function(){ 
            if(this.selectedVariableId) this.onVariableChange();
            else if(this.selectedGroupId) this.onGroupChange();
        }, 200)
    };
}

/* ================== Helpers (same as earlier) =================== */

function detectDistribution(values, valueType='auto'){
    if(!values || values.length === 0) return { name: null, params:{}, reason: 'no data' };

    if(valueType === 'percent'){
        const needScale = values.some(v => v > 1);
        const scaled = needScale ? values.map(v=>v/100) : values.slice();
        const m = mean(scaled), v = variance(scaled);
        const common = (m*(1-m)/ (v || 1e-9)) - 1;
        const a = Math.max(0.01, m*common);
        const b = Math.max(0.01, (1-m)*common);
        return { name: 'beta', params: { a, b }, reason: 'percent scaled to 0–1 → Beta' };
    }

    const m = mean(values), v = variance(values), s = skewness(values);
    const allInt = values.every(x => Number.isInteger(x) && x >= 0);

    if(allInt && Math.abs(v - m) <= Math.max(1, 0.2*m)) {
        return { name: 'poisson', params: { lambda: Math.max(0.001,m) }, reason: 'count data → Poisson' };
    }

    if(s > 0.8 && values.every(v => v > 0)) {
        const logs = values.map(x => Math.log(x));
        return { name: 'lognormal', params: { mu: mean(logs), sigma: Math.sqrt(variance(logs)) }, reason: 'positive skew → Lognormal' };
    }

    return { name: 'normal', params: { mu: m, sigma: Math.sqrt(v||0.000001) }, reason: 'fallback → Normal' };
}

function sampleNormal(mu=0,sigma=1){
    let u=0,v=0;
    while(u===0) u=Math.random();
    while(v===0) v=Math.random();
    return mu + sigma * Math.sqrt(-2*Math.log(u)) * Math.cos(2*Math.PI*v);
}
function samplePoisson(lambda){
    const L = Math.exp(-lambda);
    let p=1,k=0;
    do { k++; p *= Math.random(); } while(p > L);
    return k-1;
}
function sampleLogNormal(mu,sigma){ return Math.exp(sampleNormal(mu,sigma)); }
function sampleBetaApprox(a,b){
    const x = Math.pow(Math.random(), 1/a);
    const y = Math.pow(Math.random(), 1/b);
    const sum = x + y;
    return sum === 0 ? 0 : x / sum;
}

function runMonteCarlo(distInfo, n=5000){
    const out = new Array(n);
    for(let i=0;i<n;i++){
        switch(distInfo.name){
            case 'poisson': out[i] = samplePoisson(distInfo.params.lambda || 1); break;
            case 'lognormal': out[i] = sampleLogNormal(distInfo.params.mu || 0, distInfo.params.sigma || 1); break;
            case 'beta': out[i] = sampleBetaApprox(distInfo.params.a || 1, distInfo.params.b || 1); break;
            case 'normal':
            default: out[i] = sampleNormal(distInfo.params.mu || 0, distInfo.params.sigma || 1);
        }
    }
    return out;
}

function histogramBuckets(samples, bins=40){
    if(!samples || samples.length===0) return [];

    const min = Math.min(...samples), max = Math.max(...samples);
    if(min === max){
        return [{ start: min-0.5, end: min+0.5, label: (min.toString()), count: samples.length }];
    }

    const range = max - min;
    const step = range / bins;
    const buckets = Array.from({length: bins}, (_,i)=>({
        start: min + i*step,
        end: min + (i+1)*step,
        label: `B${i+1}`,
        count: 0
    }));

    for(const s of samples){
        let idx = Math.floor((s - min) / step);
        if(!isFinite(idx) || idx < 0) idx = 0;
        if(idx >= bins) idx = bins-1;
        buckets[idx].count++;
    }
    return buckets;
}

function kdeGrid(samples, points=200){
    if(!samples || samples.length===0) return { x:[], density:[] };

    const min = Math.min(...samples), max = Math.max(...samples);
    const range = max - min || 1;
    const grid = new Array(points);
    for(let i=0;i<points;i++){
        grid[i] = min + (i/(points-1))*range;
    }

    const s = std(samples) || 1e-6;
    const n = samples.length;
    const bw = 1.06 * s * Math.pow(n, -1/5);

    const density = grid.map(x => {
        let sum = 0;
        for(const v of samples){
            const z = (x - v) / (bw || 1e-6);
            sum += Math.exp(-0.5 * z*z);
        }
        return sum / (Math.sqrt(2*Math.PI) * (bw||1e-6) * n);
    });

    return { x: grid, density };
}

function mean(a){ if(!a||a.length===0) return 0; return a.reduce((s,x)=>s+x,0)/a.length; }
function variance(a){ if(!a||a.length<2) return 0; const m=mean(a); return a.reduce((s,x)=>s+(x-m)**2,0)/(a.length-1); }
function std(a){ return Math.sqrt(variance(a)); }
function skewness(a){ if(!a||a.length<3) return 0; const m=mean(a); const s=std(a)||1; return a.reduce((t,x)=>t+((x-m)/s)**3,0)/a.length; }
function quantile(arr,q){ if(!arr||arr.length===0) return null; const s=[...arr].sort((a,b)=>a-b); const pos=(s.length-1)*q; const b=Math.floor(pos); const r=pos-b; return s[b+1]!==undefined ? s[b] + r*(s[b+1]-s[b]) : s[b]; }
function cvar(arr,q){ if(!arr||arr.length===0) return null; const cut = quantile(arr,q); const tail = arr.filter(x=>x>=cut); return tail.length ? mean(tail) : cut; }

function debounce(fn, wait){
    let t; return function(...args){ clearTimeout(t); t = setTimeout(()=>fn.apply(this, args), wait); };
}
</script>
@endsection
