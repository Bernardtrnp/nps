<?php

namespace App\Services;

class RiskSummaryService
{
    /**
     * Build summary information from rows
     */
    public function buildSummary(array $rows): array
    {
        $vals = [];
        foreach ($rows as $r) {
            if (isset($r['value_num']) && is_numeric($r['value_num'])) $vals[] = floatval($r['value_num']);
        }

        $count = count($vals);
        $sum = $count ? array_sum($vals) : 0;
        $avg = $count ? ($sum / $count) : 0;
        $min = $count ? min($vals) : 0;
        $max = $count ? max($vals) : 0;

        return [
            'count' => $count,
            'sum' => round($sum,2),
            'avg' => round($avg,2),
            'min' => round($min,2),
            'max' => round($max,2),
            'insight_ai' => "Total nilai {$sum}, rata-rata ".round($avg,2).".",
            'recommendations' => $this->simpleAdvice($vals)
        ];
    }

    private function simpleAdvice(array $vals): array
    {
        if (count($vals) < 4) return ['Data sedikit — verifikasi input.'];
        $stdev = $this->stdev($vals);
        if ($stdev > (array_sum($vals)/count($vals)) * 0.5) {
            return ['Tingkat volatilitas tinggi — lakukan root cause analysis.'];
        }
        return ['Risiko relatif stabil — monitoring berkala.'];
    }

    private function stdev(array $a)
    {
        $n = count($a);
        if ($n < 2) return 0;
        $mean = array_sum($a)/$n;
        $sum = 0;
        foreach ($a as $v) $sum += ($v-$mean)*($v-$mean);
        return sqrt($sum/($n-1));
    }
}
