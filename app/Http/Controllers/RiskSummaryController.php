<?php

namespace App\Http\Controllers;

use App\Models\Risk;
use Illuminate\Support\Collection;

class RiskSummaryController extends Controller
{
    public function index()
    {
        $ranking = $this->buildRanking();

        return view('risk.summary', [
            'ranking' => $ranking,
        ]);
    }

    /**
     * Build CVaR-based ranking of all risks.
     */
    private function buildRanking()
    {
        $rows = [];

        $risks = Risk::with(['type', 'unit', 'entitas', 'variables.values'])
            ->get();

        foreach ($risks as $risk) {

            // Collect historical values
            $values = collect();
            foreach ($risk->variables as $var) {
                foreach ($var->values as $rv) {
                    if ($rv->value !== null) {
                        $values->push($rv->value);
                    }
                }
            }

            if ($values->count() < 3) {
                continue; // too few data points
            }

            $count  = $values->count();
            $sorted = $values->sort()->values();
            $latest = $values->last();
            $trend  = $latest - $values->first();

            // CVaR = Expected Shortfall at 5%
            $sliceCount = max(1, floor($count * 0.05));
            $cvar = $sorted->slice(0, $sliceCount)->avg();

            $rows[] = [
                'risk_name' => $risk->name,
                'risk_type' => $risk->type->name,
                'unit'      => $risk->unit->name ?? null,
                'entitas'   => $risk->entitas->name ?? null,
                'cvar'      => round($cvar, 2),
                'latest'    => round($latest, 2),
                'trend'     => round($trend, 2),
                'count'     => $count,
            ];
        }

        // Sort descending by CVaR — highest risk first
        return collect($rows)
            ->sortByDesc('cvar')
            ->values()
            ->toArray();
    }
}
