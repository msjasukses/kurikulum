<?php

namespace App\Http\Controllers\Setting;

use App\Exports\JamMengajarTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\JamMengajarImport;
use App\Models\TingkatKelas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class JamMengajarImportController extends Controller
{
    public function form(): View
    {
        return view('settings.jam-mengajar-import', [
            'tingkatList' => TingkatKelas::orderBy('urutan')->pluck('nama'),
            'hariList' => JamMengajarImport::HARI,
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        $import = new JamMengajarImport();
        Excel::import($import, $request->file('file'));

        return redirect()->route('setting.jam-mengajar-import.form')
            ->with('import_berhasil', $import->berhasil)
            ->with('import_gagal', $import->gagal);
    }

    public function template(): BinaryFileResponse
    {
        return Excel::download(new JamMengajarTemplateExport(), 'template-import-jam-mengajar.xlsx');
    }
}
