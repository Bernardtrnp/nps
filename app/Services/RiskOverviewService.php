<?php

namespace App\Services;

use App\Services\RiskParserService;
use App\Services\RiskAggregationService;
use App\Services\RiskAnalysisService;
use App\Services\RiskChartService;

class RiskOverviewService
{
    protected $parser;
    protected $agg;
    protected $analysis;
    protected $chart;

    public function __construct(
        RiskParserService $parser,
        RiskAggregationService $agg,
        RiskAnalysisService $analysis,
        RiskChartService $chart
    ) {
        $this->parser   = $parser;
        $this->agg      = $agg;
        $this->analysis = $analysis;
        $this->chart    = $chart;
    }

    /**
     * ENTRY POINT
     */
    public function buildPayload()
    {
        $rows = $this->parser->loadRiskRows();                 // Ambil semua raw data
        $grouped = $this->agg->apply($rows);                   // Terapkan aturan agregasi
        $analyzed = $this->analysis->run($grouped);            // SUM/AVG/MIN/MAX + Insight
        $final = $this->chart->prepare($analyzed);             // Format untuk Chart.js

        return $final;
    }
}
