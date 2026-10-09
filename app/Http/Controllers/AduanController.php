<?php

namespace App\Http\Controllers;

use App\Models\Aduan;
use App\Support\Media;
use App\Support\PublicSite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Form Aduan sederhana (issue #19, istilah kanonis `Aduan` di mana-mana).
 *
 * KEPUTUSAN BUILD (MVP): submit DISIMPAN SEDERHANA ke tabel `aduans`
 * dengan scoping per-desa. TIDAK ada workflow disposisi/penugasan/status
 * di MVP (fase 2 dengan desain + spec tersendiri). TIDAK ada Filament
 * resource untuk Aduan di MVP — petugas membaca langsung dari database
 * sampai kanal penanganan diputuskan.
 *
 * Foto ikut aturan media #13: tipe gambar umum, batas 5 MB.
 */
class AduanController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['nullable', 'string', 'max:100'],
            'kontak' => ['nullable', 'string', 'max:50'],
            'pesan' => ['required', 'string', 'min:3', 'max:5000'],
            'lokasi' => ['required', 'string', 'max:255'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $desa = PublicSite::currentDesa();
        abort_unless($desa !== null, 404);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = Media::storeUploaded($request->file('foto'), 'aduan');
        }

        Aduan::withoutGlobalScope('desa')->create([
            'desa_id' => $desa->id,
            'nama' => $validated['nama'] ?? null,
            'kontak' => $validated['kontak'] ?? null,
            'pesan' => $validated['pesan'],
            'foto_path' => $fotoPath,
            'lokasi' => $validated['lokasi'],
        ]);

        return back()->with('status', 'Aduan Anda terkirim. Petugas akan meninjau laporan tersebut.');
    }
}
