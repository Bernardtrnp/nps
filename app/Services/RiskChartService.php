<?php
namespace App\Services;

class RiskChartService
{
    /**
     * Slight augmentation for UI: add color_key; client will generate colors from label.
     */
    public function prepareForChart(array $grouped)
    {
        foreach ($grouped['datasets'] as &$ds) {
            if (!isset($ds['color_key'])) $ds['color_key'] = $ds['label'];
            if (!isset($ds['stack'])) $ds['stack'] = null;
        }
        return $grouped;
    }
}
