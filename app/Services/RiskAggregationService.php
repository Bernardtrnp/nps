<?php
namespace App\Services;

use Illuminate\Support\Collection;

class RiskAggregationService
{
    /**
     * Apply category rules (entitas/unit/subcategory fallback) to each row.
     */
    public function applyCategoryAggregationRules(array $rows): array
    {
        foreach ($rows as &$r) {
            $unit = $r['unit'] ?? null;
            $ent = $r['entitas'] ?? null;
            $subcat = $r['subcategory'] ?? null;

            if (!empty($unit) && !empty($subcat)) {
                $r['category_key'] = "UNIT:{$unit} — {$subcat}";
                $r['category_label'] = "{$unit} — {$subcat}";
                continue;
            }

            if (!empty($ent) && !empty($subcat)) {
                $r['category_key'] = "ENT:{$ent} — {$subcat}";
                $r['category_label'] = "{$ent} — {$subcat}";
                continue;
            }

            if (!empty($ent) && empty($unit) && empty($subcat)) {
                $r['category_key'] = "ENT:{$ent}";
                $r['category_label'] = "{$ent}";
                continue;
            }

            if (!empty($unit) && empty($ent) && empty($subcat)) {
                $r['category_key'] = "UNIT:{$unit}";
                $r['category_label'] = "{$unit}";
                continue;
            }

            if (!empty($subcat) && empty($unit) && empty($ent)) {
                $r['category_key'] = "SUB:{$subcat}";
                $r['category_label'] = "{$subcat}";
                continue;
            }

            // fallback
            $r['category_key'] = "RISK:".$r['risk_name'];
            $r['category_label'] = $r['risk_name'];
        }

        return $rows;
    }

    /**
     * Build panel payload tailored for given risk type name following your specs.
     * returns array with ['labels','datasets','years','default_x_mode']
     */
    public function buildPanelForType(string $riskTypeName, array $rows): array
    {
        // safety
        $rows = array_values($rows ?? []);
        // apply category rules first (ensures category_key exists)
        $rows = $this->applyCategoryAggregationRules($rows);

        // collect years
        $years = collect($rows)->pluck('year')->filter()->unique()->sort()->values()->all();

        // default output
        $out = [
            'labels' => [],
            'datasets' => [],
            'years' => $years,
            'default_x_mode' => 'year'
        ];

        // route to specialized handlers
        $rname = strtolower(trim($riskTypeName ?? ''));

        if (str_contains($rname, 'hse')) {
            return $this->panelHSE($rows, $out);
        }

        if (str_contains($rname, 'shortfall') && str_contains($rname, 'pemenuhan')) {
            return $this->panelHRShortfallPemenuhan($rows, $out);
        }

        if (str_contains($rname, 'shortfall') && str_contains($rname, 'kompetensi') && str_contains($rname, 'pembangkit')) {
            return $this->panelHRKompetensiPembangkit($rows, $out);
        }

        if (str_contains($rname, 'kompetensi') && str_contains($rname, 'unit usaha')) {
            return $this->panelHRKompetensiUnitUsaha($rows, $out);
        }

        if (str_contains($rname, 'realisasi biaya')) {
            return $this->panelHRRealisasiBiaya($rows, $out);
        }

        if (str_contains($rname, 'sla')) {
            return $this->panelSLA($rows, $out);
        }

        if (str_contains($rname, 'diversifikasi') || str_contains($rname,'revenue')) {
            return $this->panelDiversifikasiRevenue($rows, $out);
        }

        if (str_contains($rname, 'finance') || str_contains($rname,'cashflow')) {
            return $this->panelFinanceCashflow($rows, $out);
        }

        if (str_contains($rname, 'project')) {
            return $this->panelProject($rows, $out);
        }

        // fallback: generic yearly grouped by category_key
        return $this->genericYearlyByCategory($rows, $out);
    }

    /* ---------------------- Handlers ---------------------- */

    private function panelHSE(array $rows, array $out)
    {
        // X: year (aggregate all months/quarters into year)
        $grouped = $this->sumBy(['category_key'], ['year'], $rows);
        $labels = $this->sortedX(array_keys($grouped['x_map']));
        $datasets = [];
        foreach ($grouped['cats'] as $cat => $map) {
            $data = [];
            foreach ($labels as $lab) $data[] = $map[$lab] ?? 0;
            $datasets[] = ['label' => $cat, 'data' => $data, 'stack' => 'stack1'];
        }
        return ['labels'=>$labels,'datasets'=>$datasets,'years'=>$out['years'],'default_x_mode'=>'year'];
    }

    private function panelHRShortfallPemenuhan(array $rows, array $out)
    {
        // X: year-month (YYYY-MM) — ensure months exist — provide years for dropdown
        // convert all rows to YYYY-MM
        $converted = array_map(function($r){
            if ($r['x_type']==='year-month') return $r;
            // if quarter or year, convert to default month '01' for year or Q1->month 01
            if ($r['x_type']==='year-quarter' && !empty($r['quarter'])) {
                $m = (int)$r['quarter'] * 3 - 2; // Q1->1, Q2->4
                $r['x_key'] = sprintf('%04d-%02d', $r['year'], $m);
                $r['x_type'] = 'year-month';
                return $r;
            }
            // year only -> map to Jan
            $r['x_key'] = sprintf('%04d-01', $r['year']);
            $r['x_type'] = 'year-month';
            return $r;
        }, $rows);

        // group by category_key (category_key may be risk name/unit/etc)
        $grouped = $this->sumBy(['category_key'], ['x_key'], $converted);
        $labels = $this->sortedX(array_keys($grouped['x_map']));
        $datasets = [];
        foreach ($grouped['cats'] as $cat => $map) {
            $data = [];
            foreach ($labels as $lab) $data[] = $map[$lab] ?? 0;
            $datasets[] = ['label'=>$cat,'data'=>$data,'stack'=>null];
        }
        return ['labels'=>$labels,'datasets'=>$datasets,'years'=>$out['years'],'default_x_mode'=>'year-month'];
    }

    private function panelHRKompetensiPembangkit(array $rows, array $out)
    {
        // X: year, group per unit (category_key already holds unit if available)
        $byYear = $this->sumBy(['category_key'], ['year'], $rows);
        $labels = $this->sortedX(array_keys($byYear['x_map']));
        $datasets = [];
        foreach ($byYear['cats'] as $cat => $map) {
            $data = [];
            foreach ($labels as $lab) $data[] = $map[$lab] ?? 0;
            $datasets[] = ['label'=>$cat,'data'=>$data,'stack'=>null];
        }
        return ['labels'=>$labels,'datasets'=>$datasets,'years'=>$out['years'],'default_x_mode'=>'year'];
    }

    private function panelHRKompetensiUnitUsaha(array $rows, array $out)
    {
        // X: year, per entitas, stacked by subcategory (O&M / Non O&M)
        // Build composite keys: entitas + subcategory
        $mod = [];
        foreach ($rows as $r) {
            $ent = $r['entitas'] ?? 'Unknown';
            $sub = $r['subcategory'] ?? 'Unknown';
            $r['category_key'] = "ENT:{$ent} — {$sub}";
            $mod[] = $r;
        }
        $grouped = $this->sumBy(['category_key'], ['year'], $mod);
        $labels = $this->sortedX(array_keys($grouped['x_map']));
        $datasets = [];
        foreach ($grouped['cats'] as $cat => $map) {
            $data = [];
            foreach ($labels as $lab) $data[] = $map[$lab] ?? 0;
            // stack by entitas label (so same entitas subcategories stack)
            $stack = explode(' — ', $cat)[0] ?? $cat;
            $datasets[] = ['label'=>$cat,'data'=>$data,'stack'=>$stack];
        }
        return ['labels'=>$labels,'datasets'=>$datasets,'years'=>$out['years'],'default_x_mode'=>'year'];
    }

    private function panelHRRealisasiBiaya(array $rows, array $out)
    {
        // X: year, per subcategory
        $grouped = $this->sumBy(['subcategory'], ['year'], $rows);
        $labels = $this->sortedX(array_keys($grouped['x_map']));
        $datasets = [];
        foreach ($grouped['cats'] as $cat => $map) {
            $label = $cat ?: 'Unknown';
            $data = [];
            foreach ($labels as $lab) $data[] = $map[$lab] ?? 0;
            $datasets[] = ['label'=>$label,'data'=>$data,'stack'=>null];
        }
        return ['labels'=>$labels,'datasets'=>$datasets,'years'=>$out['years'],'default_x_mode'=>'year'];
    }

    private function panelSLA(array $rows, array $out)
    {
        // X: year-month, per unit
        $converted = array_map(function($r){
            if ($r['x_type']!=='year-month') {
                // convert quarter/year -> month fallback
                if ($r['x_type']==='year-quarter' && !empty($r['quarter'])) {
                    $m = (int)$r['quarter'] * 3 - 2;
                    $r['x_key'] = sprintf('%04d-%02d', $r['year'], $m);
                } else {
                    $r['x_key'] = sprintf('%04d-01', $r['year']);
                }
                $r['x_type'] = 'year-month';
            }
            return $r;
        }, $rows);

        $grouped = $this->sumBy(['category_key'], ['x_key'], $converted);
        $labels = $this->sortedX(array_keys($grouped['x_map']));
        $datasets = [];
        foreach ($grouped['cats'] as $cat => $map) {
            $data = [];
            foreach ($labels as $lab) $data[] = $map[$lab] ?? 0;
            $datasets[] = ['label'=>$cat,'data'=>$data,'stack'=>null];
        }
        return ['labels'=>$labels,'datasets'=>$datasets,'years'=>$out['years'],'default_x_mode'=>'year-month'];
    }

    private function panelDiversifikasiRevenue(array $rows, array $out)
    {
        // X: year, per entitas (PLN group / non PLn / anak)
        // Use entitas; if not exist fallback category_key
        $mod = [];
        foreach ($rows as $r) {
            $ent = $r['entitas'] ?? null;
            if ($ent) {
                $r['category_key'] = "ENT:{$ent}";
            } else {
                $r['category_key'] = $r['category_key'] ?? 'Unknown';
            }
            $mod[] = $r;
        }
        $grouped = $this->sumBy(['category_key'], ['year'], $mod);
        $labels = $this->sortedX(array_keys($grouped['x_map']));
        $datasets = [];
        foreach ($grouped['cats'] as $cat => $map) {
            $data = [];
            foreach ($labels as $lab) $data[] = $map[$lab] ?? 0;
            $datasets[] = ['label'=>$cat,'data'=>$data,'stack'=>null];
        }
        return ['labels'=>$labels,'datasets'=>$datasets,'years'=>$out['years'],'default_x_mode'=>'year'];
    }

    private function panelFinanceCashflow(array $rows, array $out)
    {
        // X: year-month, per subcategory (data collection period, cash ratio, CFO)
        $converted = array_map(function($r){
            if ($r['x_type']!=='year-month') {
                if ($r['x_type']==='year-quarter' && !empty($r['quarter'])) {
                    $m = (int)$r['quarter'] * 3 - 2;
                    $r['x_key'] = sprintf('%04d-%02d', $r['year'], $m);
                } else {
                    $r['x_key'] = sprintf('%04d-01', $r['year']);
                }
                $r['x_type'] = 'year-month';
            }
            return $r;
        }, $rows);

        $grouped = $this->sumBy(['subcategory'], ['x_key'], $converted);
        $labels = $this->sortedX(array_keys($grouped['x_map']));
        $datasets = [];
        foreach ($grouped['cats'] as $cat => $map) {
            $label = $cat ?: 'Unknown';
            $data = [];
            foreach ($labels as $lab) $data[] = $map[$lab] ?? 0;
            $datasets[] = ['label'=>$label,'data'=>$data,'stack'=>null];
        }
        return ['labels'=>$labels,'datasets'=>$datasets,'years'=>$out['years'],'default_x_mode'=>'year-month'];
    }

    private function panelProject(array $rows, array $out)
    {
        // X: year, per risk_name OH/NON OH, stacked by status (assume subcategory indicates status 'Ongoing'/'Done')
        // build composite keys: risk_name + status
        $mod = [];
        foreach ($rows as $r) {
            $riskName = $r['risk_name'] ?? 'Unknown';
            $status = $r['subcategory'] ?? 'Unknown';
            $r['category_key'] = strtoupper(trim($riskName))." — ".ucfirst(strtolower($status));
            $mod[] = $r;
        }
        $grouped = $this->sumBy(['category_key'], ['year'], $mod);
        $labels = $this->sortedX(array_keys($grouped['x_map']));

        // we want datasets grouped by risk_name with stacks by risk_name so that within each risk_name
        // different statuses stack. We'll set dataset.stack = risk_name
        $datasets = [];
        foreach ($grouped['cats'] as $cat => $map) {
            $data = [];
            foreach ($labels as $lab) $data[] = $map[$lab] ?? 0;
            $riskName = explode(' — ', $cat)[0] ?? $cat;
            $datasets[] = ['label'=>$cat,'data'=>$data,'stack'=>$riskName];
        }
        return ['labels'=>$labels,'datasets'=>$datasets,'years'=>$out['years'],'default_x_mode'=>'year'];
    }

    private function genericYearlyByCategory(array $rows, array $out)
    {
        $grouped = $this->sumBy(['category_key'], ['year'], $rows);
        $labels = $this->sortedX(array_keys($grouped['x_map']));
        $datasets = [];
        foreach ($grouped['cats'] as $cat => $map) {
            $data = [];
            foreach ($labels as $lab) $data[] = $map[$lab] ?? 0;
            $datasets[] = ['label'=>$cat,'data'=>$data,'stack'=>null];
        }
        return ['labels'=>$labels,'datasets'=>$datasets,'years'=>$out['years'],'default_x_mode'=>'year'];
    }

    /* ---------------------- Utilities ---------------------- */

    /**
     * Sum by given category keys and x_keys.
     * categoryKeys: fields to combine for category label (e.g. ['category_key'] or ['entitas','subcategory'])
     * xFields: which x field to use for x grouping (e.g. ['x_key'] or ['year'])
     *
     * returns [
     *   'cats' => [ category_label => [ x_key => sum ] ],
     *   'x_map' => [ x_key => true ]
     * ]
     */
    private function sumBy(array $categoryKeys, array $xFields, array $rows)
    {
        $cats = [];
        $xmap = [];
        foreach ($rows as $r) {
            // build category label
            $parts = [];
            foreach ($categoryKeys as $ck) {
                $parts[] = $r[$ck] ?? '';
            }
            $cat = implode(' | ', array_filter($parts, fn($v)=>$v!==null && $v!==''));

            if ($cat === '') $cat = $r['category_label'] ?? ($r['risk_name'] ?? 'Unknown');

            // pick x key (first provided)
            $x = $r[$xFields[0]] ?? ($r['x_key'] ?? '');
            if ($x === '') continue; // skip if no x

            $val = (float)($r['value_num'] ?? 0);

            if (!isset($cats[$cat])) $cats[$cat] = [];
            if (!isset($cats[$cat][$x])) $cats[$cat][$x] = 0;
            $cats[$cat][$x] += $val;
            $xmap[$x] = true;
        }

        return ['cats'=>$cats, 'x_map'=>$xmap];
    }

    private function sortedX(array $keys)
    {
        // sort using patterns
        usort($keys, function($a,$b){
            // YYYY-MM
            if (preg_match('/^(\d{4})-(\d{2})$/',$a,$ma) && preg_match('/^(\d{4})-(\d{2})$/',$b,$mb)){
                return intval($ma[1].$ma[2]) <=> intval($mb[1].$mb[2]);
            }
            // YYYY-Qn
            if (preg_match('/^(\d{4})-Q(\d)$/i',$a,$ma) && preg_match('/^(\d{4})-Q(\d)$/i',$b,$mb)){
                return intval($ma[1].'0'.$ma[2]) <=> intval($mb[1].'0'.$mb[2]);
            }
            // YYYY only
            if (preg_match('/^(\d{4})$/',$a,$ma) && preg_match('/^(\d{4})$/',$b,$mb)){
                return intval($ma[1]) <=> intval($mb[1]);
            }
            // mixed types: try numeric year compare if present
            $ay = intval(substr($a,0,4)); $by = intval(substr($b,0,4));
            return $ay <=> $by;
        });
        return $keys;
    }
}
