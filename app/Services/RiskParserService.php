<?php

namespace App\Services;

use App\Models\Risk;

class RiskParserService
{
    public function loadRiskRows(array $riskTypeIds = []): array
    {
        // --- FIX: always flatten nested arrays ---
        $riskTypeIds = collect($riskTypeIds)
            ->flatten()
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $q = Risk::with([
            'type',
            'unit',
            'entitas',
            'variables.values'
        ]);

        if (!empty($riskTypeIds)) {
            $q->whereIn('risk_type_id', $riskTypeIds);
        }

        $risks = $q->get();
        $out = [];

        foreach ($risks as $risk) {
            $riskTypeId   = $risk->risk_type_id;
            $riskTypeName = $risk->type?->name ?? null;
            $riskName     = $risk->name ?? null;
            $unitName     = $risk->unit?->name ?? null;
            $entitasName  = $risk->entitas?->name ?? null;

            foreach ($risk->variables as $var) {
                foreach ($var->values as $val) {

                    // convert value text to float
                    $valueNumber = $this->normalizeValue($val->value);

                    // determine X axis key
                    $xKey = $this->determineXKey($val);

                    $out[] = [
                        'risk_type_id'   => $riskTypeId,
                        'risk_type_name' => $riskTypeName,
                        'risk_name'      => $riskName,
                        'unit'           => $unitName,
                        'entitas'        => $entitasName,
                        'subcategory'    => $var->subcategory,

                        'year'    => $val->year,
                        'quarter' => $val->quarter,
                        'month'   => $val->month,

                        'x_key'      => $xKey,
                        'value_num'  => $valueNumber
                    ];
                }
            }
        }

        return $out;
    }

    private function normalizeValue($value)
    {
        if ($value === null) return 0;

        $clean = str_replace(['%', ','], ['', '.'], trim($value));

        return is_numeric($clean) ? floatval($clean) : 0;
    }

    private function determineXKey($val)
    {
        if ($val->year && !$val->quarter && !$val->month) {
            return (string) $val->year;
        }

        if ($val->year && $val->quarter && !$val->month) {
            return $val->year . '-Q' . $val->quarter;
        }

        if ($val->year && $val->month && !$val->quarter) {
            return $val->year . '-' . str_pad($val->month, 2, '0', STR_PAD_LEFT);
        }

        if ($val->year && $val->quarter && $val->month) {
            return $val->year . '-Q' . $val->quarter . '-' . str_pad($val->month, 2, '0', STR_PAD_LEFT);
        }

        return 'Unknown';
    }
}
