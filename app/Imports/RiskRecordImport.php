<?php

namespace App\Imports;

use App\Models\Risk;
use App\Models\RiskType;
use App\Models\Entitas;
use App\Models\Unit;
use App\Models\RiskVariable;
use App\Models\RiskValue;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class RiskRecordImport implements OnEachRow, WithHeadingRow
{
    public function headingRow(): int
    {
        return 1;
    }

    /* -----------------------------------------------------------
     * NORMALIZATION HELPERS
     * ----------------------------------------------------------- */

    private function normalizeKey($rawKey)
    {
        if (!$rawKey) return null;

        // remove invisible/UTF controls
        $key = preg_replace('/[^\x20-\x7E]/', '', $rawKey);
        $key = strtolower(trim($key));

        // remove separators
        $key = str_replace([' ', '-', '_', '/', '\\'], '', $key);

        return preg_replace('/[^a-z0-9]/', '', $key);
    }

    private function normalizeValue(?string $v): ?string
    {
        if ($v === null) return null;

        $s = preg_replace('/[^\x20-\x7E]/', '', $v);
        $s = preg_replace('/\s+/', ' ', trim($s));

        return ($s === '') ? null : $s;
    }

    /* -----------------------------------------------------------
     * HEADER MAPPING — NOW INCLUDES SUBCATEGORY FIX
     * ----------------------------------------------------------- */

    private function mapHeader($cleanKey)
    {
        $map = [
            // time
            'year' => 'year',
            'tahun' => 'year',
            'quarter' => 'quarter',
            'quartal' => 'quarter',
            'month' => 'month',
            'bulan' => 'month',

            // unit
            'unit' => 'unit_name',
            'namaunit' => 'unit_name',
            'unitname' => 'unit_name',

            // entitas
            'entitas' => 'entitas_name',
            'namaentitas' => 'entitas_name',
            'entitasname' => 'entitas_name',

            // risk
            'risktype' => 'risk_type',
            'risktype' => 'risk_type',
            'risk_name' => 'risk_name',
            'riskname' => 'risk_name',

            // 🔥 SUBCATEGORY FIX
            'subcategory' => 'subcategory',
            'subkategori' => 'subcategory',
            'kategoriinsiden' => 'subcategory',

            // variable
            'variable' => 'variable',
            'variabel' => 'variable',

            // value
            'value' => 'value',
            'nilai' => 'value',

            // unit value
            'unitvalue' => 'unit_value',
            'unit_value' => 'unit_value',

            // variable metadata
            'projectname' => 'project_name',
            'value_type' => 'value_type',
            'valuetype' => 'value_type',
            'timedimension' => 'time_dimension',

            // metadata
            'method' => 'method',
            'source' => 'source',
            'notes' => 'notes',
            'note'  => 'notes',

            // Excel raw metadata
            'metadata' => '__risk_metadata',
        ];

        return $map[$cleanKey] ?? null;
    }

    /* -----------------------------------------------------------
     * MAIN IMPORT LOGIC
     * ----------------------------------------------------------- */

    public function onRow(Row $row)
    {
        $raw = $row->toArray();
        $data = [];
        $excelRawMetadata = null;

        /* -------------------------------
         * STEP 1: PARSE HEADERS
         * ------------------------------- */
        foreach ($raw as $key => $value) {
            if (!$key) continue;
            $clean = $this->normalizeKey($key);
            $field = $this->mapHeader($clean);

            if ($field === '__risk_metadata') {
                $excelRawMetadata = is_string($value) ? trim($value) : $value;
                continue;
            }

            if ($field) {
                $data[$field] = is_string($value) ? trim($value) : $value;
            }
        }

        /* -------------------------------
         * STEP 2: VALIDATION
         * ------------------------------- */
        if (empty($data['risk_type']) || empty($data['risk_name']) ||
            empty($data['variable'])   || !isset($data['value']))
        {
            return; // skip invalid rows
        }

        /* -------------------------------
         * STEP 3: PARSE MONTH / QUARTER
         * ------------------------------- */
        $month   = $this->parseMonth($data['month'] ?? null);
        $quarter = $this->parseQuarter($data['quarter'] ?? null);

        /* -------------------------------
         * STEP 4: RISK TYPE
         * ------------------------------- */
        $riskType = RiskType::firstOrCreate([
            'name' => $data['risk_type']
        ]);

        /* -------------------------------
         * STEP 5: UNIT / ENTITAS
         * ------------------------------- */
        $entitasName = $this->normalizeValue($data['entitas_name'] ?? null);
        $unitName    = $this->normalizeValue($data['unit_name'] ?? null);

        $entitas = $entitasName
            ? Entitas::firstOrCreate(['name' => $entitasName])
            : null;

        $unit = $unitName
            ? Unit::firstOrCreate(
                ['name' => $unitName],
                ['entitas_id' => $entitas?->id]
              )
            : null;

        /* -------------------------------
         * STEP 6: CREATE / FIND RISK
         * ------------------------------- */
        $risk = Risk::firstOrCreate([
            'risk_type_id' => $riskType->id,
            'unit_id'      => $unit?->id,
            'entitas_id'   => $entitas?->id,
            'name'         => $data['risk_name'],
        ]);

        /* -------------------------------
         * STEP 7: SAVE SUBCATEGORY (FIX)
         * ------------------------------- */
        if (!empty($data['subcategory'])) {
            $risk->subcategory = $data['subcategory'];
            $risk->save();
        }

        /* -------------------------------
         * STEP 8: MERGE EXCEL METADATA
         * ------------------------------- */
        if (!empty($excelRawMetadata)) {
            $decoded = null;
            if (is_string($excelRawMetadata)) {
                $maybeJson = trim($excelRawMetadata);
                $tmp = json_decode($maybeJson, true);
                if ($tmp !== null && json_last_error() === JSON_ERROR_NONE) {
                    $decoded = $tmp;
                }
            } elseif (is_array($excelRawMetadata)) {
                $decoded = $excelRawMetadata;
            }

            $existing = is_array($risk->metadata)
                ? $risk->metadata
                : (is_string($risk->metadata) ? json_decode($risk->metadata, true) ?? [] : []);

            $merged = $decoded !== null
                ? array_merge($existing, $decoded)
                : array_merge($existing, ['excel_metadata' => $excelRawMetadata]);

            $risk->metadata = $merged;
            $risk->save();
        }

        /* -------------------------------
         * STEP 9: STORE UNKNOWN COLUMNS INTO METADATA
         * ------------------------------- */
        $extraMeta = [];
        foreach ($raw as $rawKey => $rawValue) {
            $clean = $this->normalizeKey($rawKey);
            if ($clean && $this->mapHeader($clean) === null) {
                $extraMeta[$clean] = is_string($rawValue) ? trim($rawValue) : $rawValue;
            }
        }

        if (!empty($extraMeta)) {
            $existing = is_array($risk->metadata)
                ? $risk->metadata
                : (is_string($risk->metadata) ? json_decode($risk->metadata, true) ?? [] : []);
            $risk->metadata = array_merge($existing, $extraMeta);
            $risk->save();
        }

        /* -------------------------------
         * STEP 10: RISK VARIABLE
         * ------------------------------- */
        $variable = RiskVariable::firstOrCreate([
            'risk_id' => $risk->id,
            'variable_name' => $data['variable'],
        ]);

        // write only if empty
        $knownVarFields = [
            'unit_value'     => $data['unit_value'] ?? null,
            'value_type'     => $data['value_type'] ?? null,
            'time_dimension' => $data['time_dimension'] ?? null,
            'project_name'   => $data['project_name'] ?? null,
            'method'         => $data['method'] ?? null,
            'source'         => $data['source'] ?? null,
            'notes'          => $data['notes'] ?? null,
        ];

        $updated = false;
        foreach ($knownVarFields as $col => $val) {
            if ($val !== null && empty($variable->$col)) {
                $variable->$col = $val;
                $updated = true;
            }
        }
        if ($updated) $variable->save();

        /* -------------------------------
         * STEP 11: RISK VALUE
         * ------------------------------- */
        RiskValue::create([
            'risk_variable_id' => $variable->id,
            'year'             => intval($data['year']),
            'quarter'          => $quarter,
            'month'            => $month,
            'value'            => strlen((string)($data['value'] ?? '')) ? floatval($data['value']) : null,
        ]);
    }

    /* -----------------------------------------------------------
     * UTILS
     * ----------------------------------------------------------- */

    private function parseMonth($m)
    {
        if (!$m) return null;
        if (is_numeric($m)) return intval($m);

        try {
            return Carbon::parse("1 $m")->month;
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseQuarter($q)
    {
        if (!$q) return null;
        if (is_numeric($q)) return intval($q);

        return match (strtoupper(trim($q))) {
            'Q1' => 1,
            'Q2' => 2,
            'Q3' => 3,
            'Q4' => 4,
            default => null,
        };
    }
}
