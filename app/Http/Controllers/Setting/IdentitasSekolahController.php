<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\IdentitasSekolah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class IdentitasSekolahController extends Controller
{
    public function edit(): View
    {
        $identitas = IdentitasSekolah::first() ?? new IdentitasSekolah();

        return view('settings.identitas', compact('identitas'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'npsn' => 'nullable|string|max:20',
            'nama_sekolah' => 'nullable|string|max:150',
            'alamat' => 'nullable|string',
            'kelurahan' => 'nullable|string|max:100',
            'kecamatan' => 'nullable|string|max:100',
            'kabupaten' => 'nullable|string|max:100',
            'provinsi' => 'nullable|string|max:100',
            'kode_pos' => 'nullable|string|max:10',
            'telepon' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'website' => 'nullable|string|max:100',
            'nama_kepala_sekolah' => 'nullable|string|max:100',
            'nip_kepala_sekolah' => 'nullable|string|max:30',
            'logo' => 'nullable|image|max:2048',
        ]);

        $identitas = IdentitasSekolah::first() ?? new IdentitasSekolah();

        if ($request->hasFile('logo')) {
            if ($identitas->logo) {
                Storage::disk('public')->delete($identitas->logo);
            }
            $validated['logo'] = $request->file('logo')->store('uploads/identitas_sekolah', 'public');
        } else {
            unset($validated['logo']);
        }

        $identitas->fill($validated);
        $identitas->save();

        return redirect()->route('setting.identitas.edit')->with('success', 'Identitas sekolah berhasil disimpan.');
    }
}
