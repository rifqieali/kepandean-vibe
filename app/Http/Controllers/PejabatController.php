<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\Pejabat;
use App\Support\PublicSite;
use App\Support\Seo;
use Inertia\Inertia;
use Inertia\Response;

class PejabatController extends Controller
{
    public function index(): Response
    {
        $desa = PublicSite::currentDesa();

        $pejabats = $desa instanceof Desa
            ? Pejabat::forDesa($desa)->orderBy('urutan')->orderBy('nama')->get()
            : collect();

        $groups = [];
        foreach (Pejabat::kelompokOptions() as $key => $label) {
            $groups[] = [
                'key' => $key,
                'label' => $label,
                'items' => $pejabats->where('kelompok', $key)->values()->map(fn (Pejabat $p): array => [
                    'nama' => $p->nama,
                    'jabatan' => $p->jabatan,
                    'wilayah_label' => $p->wilayah_label,
                    'foto_url' => $p->foto_path ? asset('storage/'.$p->foto_path) : null,
                ])->all(),
            ];
        }

        $title = $desa instanceof Desa ? "Struktur Organisasi {$desa->name}" : 'Struktur Organisasi';
        $description = $desa instanceof Desa
            ? "Struktur organisasi {$desa->name}: pimpinan, perangkat, dan ketua RT/RW yang bisa dihubungi warga."
            : 'Struktur organisasi desa: pimpinan, perangkat, dan ketua RT/RW.';

        return Inertia::render('struktur', [
            'groups' => $groups,
            'meta' => Seo::meta($title, $description, route('profil.struktur')),
            'schema' => Seo::profileSchema($title, route('profil.struktur'), $description),
            ...PublicSite::sharedProps($desa),
        ]);
    }
}
