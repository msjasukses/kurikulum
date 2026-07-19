<?php

namespace App\Http\Controllers\Kurikulum;

use App\Exports\PemetaanCpTpAtpTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\PemetaanCpTpAtpImport;
use App\Models\MataPelajaran;
use App\Models\TingkatKelas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PemetaanCpTpAtpImportController extends Controller
{
    public function form(): View
    {
        return view('kurikulum.cp-tp-atp-import', [
            'mapelList' => MataPelajaran::orderBy('kode_mapel')->get(['kode_mapel', 'nama_mapel']),
            'tingkatList' => TingkatKelas::orderBy('urutan')->pluck('nama'),
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        $import = new PemetaanCpTpAtpImport();
        Excel::import($import, $request->file('file'));

        return redirect()->route('kurikulum.cp-tp-atp-import.form')
            ->with('import_berhasil', $import->berhasil)
            ->with('import_gagal', $import->gagal);
    }

    public function template(): BinaryFileResponse
    {
        return Excel::download(new PemetaanCpTpAtpTemplateExport(), 'template-import-cp-tp-atp.xlsx');
    }
}
