<?php

namespace App\Helpers;

use PhpOffice\PhpSpreadsheet\Shared\Date;

class RiskValidator
{
    public static function validateRow(array $row, int $line): array
    {
        $errors = [];

        if (empty(array_filter($row))) return [];

        if (!isset($row['tahun']) || !is_numeric($row['tahun']) || $row['tahun'] < 2020 || $row['tahun'] > 2035) {
            $errors[] = "Baris $line: Tahun tidak valid.";
        }

        if (!isset($row['nilai']) || !is_numeric($row['nilai'])) {
            try {
                $timestamp = Date::excelToTimestamp($row['nilai']);
                $row['nilai'] = 0;
            } catch (\Exception $e) {
                $errors[] = "Baris $line: Nilai bukan angka.";
            }
        }

        foreach (['jenis_risiko', 'kategori', 'subkategori'] as $field) {
            if (empty($row[$field])) {
                $errors[] = "Baris $line: Kolom '$field' kosong.";
            } elseif (!self::isValidText($row[$field])) {
                $errors[] = "Baris $line: Kolom '$field' mengandung karakter terlarang.";
            }
        }

        return $errors;
    }

    public static function isValidText(string $text): bool
    {
        return preg_match('/^[a-zA-Z0-9\s\-\(\)\.,#]+$/u', $text);
    }

    public static function validateHeader(array $firstRow): array
    {
        $required = ['tahun', 'jenis_risiko', 'kategori', 'subkategori', 'nilai'];
        $missing = array_diff($required, array_map('strtolower', array_keys($firstRow)));

        return $missing ? ["Header tidak lengkap. Kolom wajib: " . implode(', ', $required)] : [];
    }

}
