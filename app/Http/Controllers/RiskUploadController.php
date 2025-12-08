<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RiskRecordImport;
use Exception;

class RiskUploadController extends Controller
{
    /**
     * Tampilkan form upload Excel
     */
    public function form()
    {
        return view('risk.upload');
    }

    /**
     * Proses upload Excel → import ke tabel risiko
     */
    public function store(Request $request)
    {
        $request->validate([
            'excel' => 'required|file|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new RiskRecordImport, $request->file('excel'));

            return back()->with('success', 'Data risiko berhasil di-import ke database!');
        } catch (Exception $e) {
            return back()->withErrors([
                'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Unduh template impor
     */
    public function downloadTemplate()
    {
        $path = storage_path('app/templates/Risk_Data_Master_Template_v2.xlsx');

        if (!file_exists($path)) {
            return back()->withErrors(['Template belum tersedia di folder storage.']);
        }

        return response()->download($path, 'Risk_Data_Master_Template.xlsx');
    }

   public function wipe()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        \DB::table('risk_values')->truncate();
        \DB::table('risk_variables')->truncate();
        \DB::table('risks')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        return back()->with('success', 'Semua data risiko berhasil dibersihkan.');
    }


}
