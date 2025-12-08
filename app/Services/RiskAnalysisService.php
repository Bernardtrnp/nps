<?php

namespace App\Services;

class RiskAnalysisService
{
    private array $rows = [];

    public function run(array $riskTypeIds = []): array
    {
        // --- FIX: Always flatten ---
        $riskTypeIds = collect($riskTypeIds)
            ->flatten()
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $this->rows = app(RiskParserService::class)->loadRiskRows($riskTypeIds);

        if (empty($this->rows)) return [];

        $grouped = [];

        foreach ($this->rows as $r) {
            $tid = $r['risk_type_id'];

            if (!isset($grouped[$tid])) {
                $grouped[$tid] = [
                    'risk_type_name' => $r['risk_type_name'],
                    'rows'           => []
                ];
            }

            $grouped[$tid]['rows'][] = $r;
        }

        $panels = [];

        foreach ($grouped as $tid => $block) {
            $rows = $block['rows'];

            $panels[$tid] = [
                'risk_type_name' => $block['risk_type_name'] ?? 'Unknown',
                'summary'        => $this->summary($rows),
                'default_tab'    => $this->detectDefaultTab($tid),

                'per_unit'     => $this->groupPerUnit($rows, $tid),
                'per_entitas'  => $this->groupPerEntitas($rows, $tid),
                'per_category' => $this->groupPerCategory($rows, $tid),

                'insight_ai'      => $this->makeInsight($rows),
                'recommendations' => $this->makeRecommendations($rows),
            ];
        }

        return $panels;
    }

    private function summary(array $rows): array
    {
        if (empty($rows)) return [
            'sum' => 0,
            'avg' => 0,
            'min' => 0,
            'max' => 0
        ];

        $vals = array_column($rows, 'value_num');

        return [
            'sum' => array_sum($vals),
            'avg' => round(array_sum($vals) / max(count($vals), 1), 2),
            'min' => min($vals),
            'max' => max($vals),
        ];
    }

    private function detectDefaultTab($tid)
    {
        // HSE
        if ($tid == 1) return 'category';

        // HR → depends on subtype
        if (in_array($tid, [2, 3, 4, 5])) return 'unit';

        // SLA
        if ($tid == 6) return 'unit';

        // Revenue Diversification
        if ($tid == 7) return 'entitas';

        // Finance
        if ($tid == 8) return 'category';

        // Project
        if ($tid == 9) return 'category';

        return 'unit';
    }

    private function groupPerUnit(array $rows, int $tid): array
    {
        $bucket = [];

        foreach ($rows as $r) {
            $unit = $r['unit'] ?? 'Unknown';

            if (!isset($bucket[$unit])) {
                $bucket[$unit] = [
                    'label' => $unit,
                    'data'  => []
                ];
            }

            $bucket[$unit]['data'][] = [
                'x' => $r['x_key'],
                'y' => $r['value_num']
            ];
        }

        return $this->formatDataset($bucket);
    }

    private function groupPerEntitas(array $rows, int $tid): array
    {
        $bucket = [];

        foreach ($rows as $r) {
            $ent = $r['entitas'] ?? 'Unknown';

            if (!isset($bucket[$ent])) {
                $bucket[$ent] = [
                    'label' => $ent,
                    'data'  => []
                ];
            }

            $bucket[$ent]['data'][] = [
                'x' => $r['x_key'],
                'y' => $r['value_num']
            ];
        }

        return $this->formatDataset($bucket);
    }

    private function groupPerCategory(array $rows, int $tid): array
    {
        $bucket = [];

        foreach ($rows as $r) {
            $cat = $r['subcategory'] ?? 'Unknown';

            if (!isset($bucket[$cat])) {
                $bucket[$cat] = [
                    'label' => $cat,
                    'data'  => []
                ];
            }

            $bucket[$cat]['data'][] = [
                'x' => $r['x_key'],
                'y' => $r['value_num']
            ];
        }

        return $this->formatDataset($bucket);
    }

    private function formatDataset(array $bucket): array
    {
        $labels = [];
        $datasets = [];

        foreach ($bucket as $name => $b) {
            foreach ($b['data'] as $point) {
                $labels[] = $point['x'];
            }
        }

        $labels = array_values(array_unique($labels));

        foreach ($bucket as $name => $b) {
            $map = [];

            foreach ($b['data'] as $point) {
                $map[$point['x']] = $point['y'];
            }

            $dataOrdered = [];

            foreach ($labels as $lbl) {
                $dataOrdered[] = $map[$lbl] ?? 0;
            }

            $datasets[] = [
                'label' => $name,
                'data'  => $dataOrdered
            ];
        }

        return [
            'labels'   => $labels,
            'datasets' => $datasets
        ];
    }

    private function makeInsight(array $rows)
    {
        $avg = $this->summary($rows)['avg'];

        return "Rata-rata nilai risiko adalah {$avg}. Tren meningkat perlu diwaspadai.";
    }

    private function makeRecommendations(array $rows)
    {
        return [
            "Perkuat monitoring risiko.",
            "Lakukan review bulanan pada unit terkait.",
            "Implementasikan rencana mitigasi lebih cepat."
        ];
    }
}
