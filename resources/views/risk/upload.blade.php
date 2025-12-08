@extends('layouts.app')

@section('content')
<div class="container">

    <h2 class="mb-4">
        <i class="bi bi-upload"></i> Import Data Risiko
    </h2>

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i>
            <strong> Berhasil!</strong> {{ session('success') }}
        </div>
    @endif

    {{-- ERROR MESSAGE --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <i class="bi bi-x-circle"></i>
            <strong> Validasi Gagal:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- PANDUAN --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-3">
                <i class="bi bi-journal-bookmark"></i> Panduan Pengisian Excel
            </h5>

            <p>File Excel harus mengikuti struktur <strong>Risk_Data_Master.xlsx</strong> dengan format:</p>

            <ul>
                <li>Baris pertama wajib berisi header.</li>
                <li>Setiap baris = satu nilai risiko.</li>
                <li>risk_type, category, risk_name wajib konsisten.</li>
                <li>value harus angka.</li>
                <li>month boleh pakai nama (Jan/January) atau angka (1).</li>
                <li>Importer otomatis:
                    <ul>
                        <li>Membuat RiskType, Unit, Entitas bila belum ada</li>
                        <li>Membuat Risk → Variable → Value</li>
                    </ul>
                </li>
            </ul>

            <hr>

            <h6 class="fw-bold">Format Header Excel:</h6>

            <div class="table-responsive mb-3">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>year</th>
                            <th>quarter</th>
                            <th>month</th>
                            <th>unit</th>
                            <th>entitas</th>
                            <th>risk_type</th>
                            <th>risk_name</th>
                            <th>category</th>
                            <th>subcategory</th>
                            <th>variable</th>
                            <th>value</th>
                            <th>unit_value</th>
                            <th>method</th>
                            <th>source</th>
                            <th>notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>2025</td>
                            <td>Q1</td>
                            <td>Jan</td>
                            <td>PLTU Bangka</td>
                            <td>Unit</td>
                            <td>SLA</td>
                            <td>Persentase Pencapaian SLA</td>
                            <td>Pencapaian SLA</td>
                            <td>-</td>
                            <td>Value</td>
                            <td>93.2</td>
                            <td>%</td>
                            <td>SLA Realisasi</td>
                            <td>Dashboard OMC</td>
                            <td>Data SLA bulan Januari</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            {{-- INFO KHUSUS UNTUK RISK TYPE PROJECT --}}
            <div class="alert alert-info mt-4">
                <i class="bi bi-info-circle"></i>
                <strong> Catatan khusus untuk Risk Type: Project</strong>

                <p class="mt-2 mb-1">
                    Risiko dengan <strong>risk_type = Project</strong> memiliki perilaku khusus:
                </p>

                <ul class="mb-0">
                    <li>
                        Kolom <code>risk_name</code> mewakili <strong>nama pekerjaan / nama project</strong> 
                        (misalnya: <em>Relokasi PLTG MPP 2x25 MW Tarahan ke Tello</em>).
                    </li>

                    <li>
                        Kolom <code>category</code> digunakan sebagai <strong>kategori proyek</strong> 
                        (misalnya: <em>Non OH</em>, <em>OH</em>, <em>Pengembangan</em>).
                    </li>

                    <li>
                        Kolom <code>subcategory</code> dipakai sebagai <strong>status proyek</strong> 
                        (misalnya: <em>On Going</em>, <em>Done</em>).
                    </li>

                    <li>
                        Dashboard analisis akan melakukan <strong>grouping otomatis</strong> untuk:
                        <ul>
                            <li>Category (Jenis proyek)</li>
                            <li>Subcategory (Status proyek)</li>
                        </ul>
                    </li>

                    <li>
                        Metadata lain seperti pemilik proyek, lokasi, atau jenis pekerjaan 
                        dapat dimasukkan dalam <code>notes</code> bila diperlukan.
                    </li>
                </ul>
            </div>
            <p class="text-muted">
                <strong>Catatan:</strong> Kolom boleh ditambah, namun kolom wajib tidak boleh dihapus.
            </p>
        </div>
    </div>


    {{-- CHECKLIST --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title">
                <i class="bi bi-check-circle"></i> Checklist Sebelum Upload
            </h5>

            <ul>
                <li>Header sudah sesuai format</li>
                <li>Tidak ada #N/A atau #REF!</li>
                <li>risk_type valid (HR, HSE, SLA, Finance, Cyber, Project, Revenue)</li>
                <li>value angka</li>
                <li>Tidak ada merge cells</li>
                <li>Format bulan konsisten</li>
            </ul>
        </div>
    </div>


    {{-- DOWNLOAD TEMPLATE --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title">
                <i class="bi bi-file-earmark"></i> Download Blanko Excel Risiko
            </h5>
            <p class="text-muted mb-2">Gunakan file ini sebagai format standar pengisian data risiko.</p>
            
            <a href="{{ route('risk.import.template') }}" class="btn btn-outline-secondary">
                <i class="bi bi-download"></i> Unduh Template Excel
            </a>
        </div>
    </div>


    {{-- FORM UPLOAD --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title">
                <i class="bi bi-upload"></i> Upload File Excel
            </h5>

            <form method="POST" action="{{ route('risk.import.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-bold">Pilih File Excel</label>
                    <input type="file" name="excel" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="bi bi-rocket-takeoff"></i> Import Sekarang
                </button>
            </form>
            <form action="{{ route('risk.import.wipe') }}" method="POST" onsubmit="return confirm('⚠️ PERINGATAN!\nIni akan menghapus SEMUA data risiko.\nLanjutkan?');">
                @csrf
                @method('DELETE')

                <button class="btn btn-danger mt-3">
                    <i class="bi bi-trash-fill"></i> Bersihkan Semua Data Risiko
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
