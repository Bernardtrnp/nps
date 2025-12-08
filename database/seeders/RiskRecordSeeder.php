<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RiskRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('risk_records')->insert([
            [
                'year' => 2025,
                'unit' => 'PLTU Bangka',
                'entity' => null,
                'risk_type' => 'HSE',
                'category' => 'K3',
                'subcategory' => 'Nearmiss',
                'value' => 272,
                'unit_measure' => 'kejadian',
                'method' => 'Proyeksi',
                'source' => 'HSE - Data Source',
                'notes' => 'Proyeksi Nearmiss 2026'
            ],
            [
                'year' => 2025,
                'unit' => null,
                'entity' => 'PLN Korporat',
                'risk_type' => 'Finance',
                'category' => 'CFO',
                'subcategory' => 'Maret',
                'value' => -175997,
                'unit_measure' => 'Rp Juta',
                'method' => 'Realisasi',
                'source' => 'Finance Cashflow',
                'notes' => 'Arus kas operasi negatif'
            ],
            // Tambahkan contoh lainnya
        ]);

    }
}