<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RiskType;
use App\Services\RiskParserService;
use App\Services\RiskAnalysisService;

class RiskAnalysisController extends Controller
{
    public function index(Request $request, RiskParserService $parser, RiskAnalysisService $analysis)
    {
        // Ambil risk type dari query param ?risk_type[]=X&risk_type[]=Y
        $selected = $request->input('risk_type', []);

        // Pastikan selalu array datarnya
        $selected = collect($selected)->flatten()->filter()->unique()->values()->toArray();

        // Ambil semua risk types untuk dropdown
        $riskTypes = RiskType::orderBy('name')->get();

        // Parsing data mentah
        $rawRows = $parser->loadRiskRows($selected);

        // Analisis & agregasi
        $payload = $analysis->run($selected);

        return view('risk.dashboard', [
            'riskTypes'         => $riskTypes,
            'selectedRiskTypes' => $selected,
            'payload'           => $payload
        ]);
    }
}
