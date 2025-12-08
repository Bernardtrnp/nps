<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RiskType;

class RiskTypeVisualizerSeeder extends Seeder
{
    public function run()
    {
        // Parent HR
        $hr = RiskType::updateOrCreate(
            ['name' => 'HR'],
            ['parent_id' => null, 'visual_x_mode' => null, 'visual_group_mode' => null]
        );

        RiskType::updateOrCreate(
            ['name' => 'HR Shortfall Pemenuhan PTK'],
            ['parent_id' => $hr->id, 'visual_x_mode' => 'year-month', 'visual_group_mode' => 'default']
        );

        RiskType::updateOrCreate(
            ['name' => 'HR Shortfall Kompetensi Karyawan Unit Pembangkit'],
            ['parent_id' => $hr->id, 'visual_x_mode' => 'year', 'visual_group_mode' => 'unit']
        );

        RiskType::updateOrCreate(
            ['name' => 'HR Kompetensi Karyawan Unit Usaha'],
            ['parent_id' => $hr->id, 'visual_x_mode' => 'year', 'visual_group_mode' => 'entitas_stacked_subcategory']
        );

        RiskType::updateOrCreate(
            ['name' => 'HR Realisasi Biaya'],
            ['parent_id' => $hr->id, 'visual_x_mode' => 'year', 'visual_group_mode' => 'subcategory']
        );

        // HSE
        RiskType::updateOrCreate(
            ['name' => 'HSE'],
            ['visual_x_mode' => 'year', 'visual_group_mode' => 'subcategory_stacked']
        );

        // SLA
        RiskType::updateOrCreate(
            ['name' => 'SLA'],
            ['visual_x_mode' => 'year-month', 'visual_group_mode' => 'unit']
        );

        // Diversifikasi Revenue
        RiskType::updateOrCreate(
            ['name' => 'Diversifikasi Revenue'],
            ['visual_x_mode' => 'year', 'visual_group_mode' => 'entitas']
        );

        // Finance Cashflow
        RiskType::updateOrCreate(
            ['name' => 'Finance Cashflow'],
            ['visual_x_mode' => 'year-month', 'visual_group_mode' => 'subcategory']
        );

        // Project
        RiskType::updateOrCreate(
            ['name' => 'Project'],
            ['visual_x_mode' => 'year', 'visual_group_mode' => 'project_stack']
        );
    }
}
