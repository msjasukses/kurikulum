<?php

namespace App\Http\Controllers;

use App\Support\TahunAjaranTerpilih;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Penukar tahun ajaran dari dropdown di topbar. Berlaku untuk semua role
 * karena hanya mengubah sudut pandang data, bukan hak aksesnya.
 */
class PilihTahunAjaranController extends Controller
{
    public function store(Request $request, TahunAjaranTerpilih $tahunAjaran): RedirectResponse
    {
        $request->validate(['tahun_ajaran_id' => 'required|integer']);

        if (! $tahunAjaran->pilih($request->input('tahun_ajaran_id'))) {
            return back()->with('error', 'Tahun ajaran yang dipilih tidak ditemukan.');
        }

        return back()->with('success', 'Tahun ajaran diubah ke '.$tahunAjaran->nama().'.');
    }
}
