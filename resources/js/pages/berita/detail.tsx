import { Link } from '@inertiajs/react';
import PageHero from '@/components/page-hero';
import PublicLayout from '@/layouts/public-layout';
import type { MetaData, SchemaData, SiteData } from '@/types/site';

interface BeritaDetail {
    judul: string;
    slug: string;
    isi: string;
    cover_url: string | null;
    kategori: { nama: string; slug: string } | null;
    tanggal: string;
    url: string;
}

interface PageProps {
    berita: BeritaDetail;
    desa: { name: string; slug: string } | null;
    meta: MetaData;
    schema?: SchemaData | null;
    site: SiteData;
}

export default function BeritaDetail({
    berita,
    desa,
    meta,
    schema,
    site,
}: PageProps) {
    const desaName = desa?.name ?? 'Desa';

    return (
        <PublicLayout meta={meta} schema={schema} site={site}>
            <PageHero
                eyebrow={`Berita ${desaName}`}
                title={berita.judul}
                site={site}
                laman="berita"
                coverUrl={berita.cover_url}
            />
            <div className="mx-auto w-full max-w-4xl flex-1 px-4 py-10 sm:px-6">
                <nav aria-label="Navigasi berita" className="mb-6">
                    <Link
                        href="/berita"
                        className="text-sm font-medium underline underline-offset-4"
                    >
                        ← Semua berita
                    </Link>
                </nav>
                <p className="mb-6 flex items-center gap-2 text-sm text-slate-500">
                    {berita.kategori && (
                        <Link
                            href={`/berita?kategori=${berita.kategori.slug}`}
                            className="rounded-full bg-slate-100 px-3 py-1 font-medium text-slate-700"
                        >
                            {berita.kategori.nama}
                        </Link>
                    )}
                    <time>{berita.tanggal}</time>
                    <span aria-hidden="true">•</span>
                    <span>{desaName}</span>
                </p>
                {berita.cover_url ? (
                    <img
                        src={berita.cover_url}
                        alt={`Cover ${berita.judul}`}
                        className="mb-8 w-full rounded-md object-cover"
                        loading="lazy"
                        decoding="async"
                    />
                ) : (
                    <div
                        aria-hidden="true"
                        className="mb-8 flex w-full items-center justify-center rounded-md bg-slate-100 py-10 text-sm text-slate-500"
                    >
                        Berita {desaName}, tanpa gambar sampul
                    </div>
                )}
                <article
                    className="max-w-none text-[15px] leading-7 [&_figure]:mb-4 [&_h1]:mb-4 [&_h1]:text-2xl [&_h1]:font-bold [&_h2]:mb-3 [&_h2]:text-xl [&_h2]:font-semibold [&_h3]:mb-2 [&_h3]:text-lg [&_h3]:font-semibold [&_img]:mb-4 [&_img]:w-full [&_img]:rounded-md [&_ol]:mb-4 [&_ol]:list-decimal [&_ol]:pl-6 [&_p]:mb-4 [&_p]:text-justify [&_ul]:mb-4 [&_ul]:list-disc [&_ul]:pl-6"
                    dangerouslySetInnerHTML={{ __html: berita.isi }}
                />
            </div>
        </PublicLayout>
    );
}
