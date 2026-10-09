<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\Profil;
use App\Support\HtmlSanitizer;
use App\Support\PublicSite;
use App\Support\Seo;
use Inertia\Inertia;
use Inertia\Response;

class ProfilController extends Controller
{
    /**
     * Satu halaman gabungan Sejarah + Visi Misi
     * (lihat design "Sejarah & Visi misi.png").
     */
    public function sejarahVisiMisi(): Response
    {
        $desa = PublicSite::currentDesa();

        $profil = $desa instanceof Desa
            ? Profil::withoutGlobalScope('desa')->where('desa_id', $desa->id)->first()
            : null;

        $title = $desa instanceof Desa ? "Sejarah & Visi Misi {$desa->name}" : 'Sejarah & Visi Misi';
        $description = $desa instanceof Desa
            ? "Sejarah {$desa->name} serta visi dan misi: arah pembangunan desa di portal resmi."
            : 'Sejarah desa serta visi dan misi di portal resmi.';

        return Inertia::render('profil/sejarah-visi-misi', [
            'profil' => [
                'sejarah' => HtmlSanitizer::clean($profil?->sejarah),
                'visi' => HtmlSanitizer::clean($profil?->visi),
                'misi' => HtmlSanitizer::fromRichOrPlain($profil?->misi),
                'foto_url' => $profil?->foto_path ? asset('storage/'.$profil->foto_path) : null,
                'isEmpty' => $profil === null || $profil->isEmpty(),
            ],
            'desa' => $desa instanceof Desa ? [
                'name' => $desa->name,
                'slug' => $desa->slug,
            ] : null,
            'meta' => Seo::meta($title, $description, route('profil.sejarah-visi-misi')),
            'schema' => Seo::profileSchema($title, route('profil.sejarah-visi-misi'), $description),
            ...PublicSite::sharedProps($desa),
        ]);
    }
}
