<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RiskType;
use App\Services\RiskAnalysisService;

class RiskOverviewController extends Controller
{
    protected $analysis;

    public function __construct(RiskAnalysisService $analysis)
    {
        $this->analysis = $analysis;
    }

    /**
     * Overview / dashboard
     */
    public function index(Request $request)
    {
        // keep same filter param name as existing UI: risk_type[]
        $selectedRiskTypes = $request->input('risk_type', []);
        if (!is_array($selectedRiskTypes) && $selectedRiskTypes !== null) {
            $selectedRiskTypes = [$selectedRiskTypes];
        }

        $riskTypes = RiskType::orderBy('name')->get();

        // run analysis pipeline
        $payload = $this->analysis->run($selectedRiskTypes);

        // pass to view
        return view('risk.dashboard', [
            'riskTypes' => $riskTypes,
            'selectedRiskTypes' => array_map('intval', $selectedRiskTypes),
            'payload' => $payload
        ]);
    }

    /**
     * flush overview (if needed)
     */
    public function flush()
    {
        // If you keep a cache layer, clear it here. For now, just redirect.
        return back()->with('success', 'Cache overview dibersihkan (jika ada).');
    }
}
