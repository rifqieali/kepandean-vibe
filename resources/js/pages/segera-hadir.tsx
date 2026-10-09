import { Link } from '@inertiajs/react';
import PageHero from '@/components/page-hero';
import SegeraHadirPanel from '@/components/segera-hadir-panel';
import PublicLayout from '@/layouts/public-layout';
import type { MetaData, SchemaData, SiteData } from '@/types/site';

interface SegeraHadirProps {
    modul: string;
    meta: MetaData;
    schema?: SchemaData | null;
    site: SiteData;
}

const LABELS: Record<string, string> = {
    pemerintahan: 'Pemerintahan',
    'struktur-organisasi': 'Struktur Organisasi',
    'lembaga-desa': 'Lembaga Desa',
    'produk-hukum': 'Produk Hukum',
    laporan: 'Laporan',
    'layanan-warga': 'Layanan Warga',
    layanan: 'Layanan',
    informasi: 'Informasi',
    berita: 'Berita Desa',
    pengumuman: 'Pengumuman',
    kegiatan: 'Kegiatan',
    'potensi-galeri': 'Potensi & Galeri',
    'kontak-lokasi': 'Kontak & Lokasi',
};

// Modul di bawah grup navigasi memakai nama grup di breadcrumb
// ("Pemerintahan › Produk Hukum", bukan slug mentah).
const GRUP: Record<string, string> = {
    pemerintahan: 'Pemerintahan',
    'lembaga-desa': 'Pemerintahan',
    'produk-hukum': 'Pemerintahan',
    laporan: 'Pemerintahan',
};

export default function SegeraHadir({
    modul,
    meta,
    schema,
    site,
}: SegeraHadirProps) {
    const label = LABELS[modul] ?? modul;
    const grup = GRUP[modul] ?? null;

    return (
        <PublicLayout meta={meta} schema={schema} site={site}>
            <PageHero
                eyebrow={
                    <>
                        <Link href="/" className="hover:underline">
                            Beranda
                        </Link>{' '}
                        › {grup && grup !== label ? <>{grup} › </> : null}
                        {label}
                    </>
                }
                title={label}
                description={
                    <>
                        Bagian dari portal resmi {site.nama} yang sedang
                        disiapkan.
                    </>
                }
                site={site}
                laman={modul}
            />
            <section className="mx-auto w-full max-w-[1440px] px-4 py-10 sm:px-8">
                <SegeraHadirPanel label={label} />
            </section>
        </PublicLayout>
    );
}
