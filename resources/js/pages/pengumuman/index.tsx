import { Link } from '@inertiajs/react';
import { Megaphone } from 'lucide-react';
import PageHero from '@/components/page-hero';
import SegeraHadirPanel from '@/components/segera-hadir-panel';
import PublicLayout from '@/layouts/public-layout';
import type { MetaData, SchemaData, SiteData } from '@/types/site';

interface PengumumanCard {
    judul: string;
    slug: string;
    excerpt: string | null;
    cover_url: string | null;
    tanggal: string;
    url: string;
}

interface PaginatorMeta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface PageProps {
    pengumuman: {
        data: PengumumanCard[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    desa: { name: string; slug: string } | null;
    meta: MetaData;
    schema?: SchemaData | null;
    site: SiteData;
}

export default function PengumumanIndex({
    pengumuman,
    desa,
    meta,
    schema,
    site,
}: PageProps) {
    const desaName = desa?.name ?? 'Desa';
    const pages: PaginatorMeta | null =
        pengumuman.last_page > 1
            ? {
                  current_page: pengumuman.current_page,
                  last_page: pengumuman.last_page,
                  per_page: pengumuman.per_page,
                  total: pengumuman.total,
              }
            : null;

    return (
        <PublicLayout meta={meta} schema={schema} site={site}>
            <PageHero
                eyebrow={`Informasi ${desaName}`}
                title={`Pengumuman ${desaName}`}
                site={site}
                laman="pengumuman"
                coverUrl={pengumuman.data[0]?.cover_url}
            />
            <div className="mx-auto w-full max-w-[1440px] flex-1 px-4 py-10 sm:px-8">
                {pengumuman.data.length > 0 ? (
                    <ul className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {pengumuman.data.map((item) => (
                            <li
                                key={item.slug}
                                className="flex min-h-[44px] flex-col overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm"
                            >
                                {item.cover_url && (
                                    <img
                                        src={item.cover_url}
                                        alt={`Cover ${item.judul}`}
                                        className="aspect-video w-full object-cover"
                                        loading="lazy"
                                        decoding="async"
                                    />
                                )}
                                <div className="flex flex-1 flex-col p-4">
                                    <p className="mb-2 flex items-center gap-2 text-xs text-slate-500">
                                        <span className="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 font-medium text-slate-700">
                                            <Megaphone
                                                className="h-3 w-3"
                                                aria-hidden="true"
                                            />
                                            Pengumuman
                                        </span>
                                        <time>{item.tanggal}</time>
                                    </p>
                                    <Link
                                        href={item.url}
                                        className="text-lg font-bold text-slate-900 underline-offset-4 hover:underline"
                                    >
                                        {item.judul}
                                    </Link>
                                    {item.excerpt && (
                                        <p className="mt-2 text-[15px] leading-7 text-slate-700">
                                            {item.excerpt}
                                        </p>
                                    )}
                                </div>
                            </li>
                        ))}
                    </ul>
                ) : (
                    <SegeraHadirPanel label="Pengumuman" />
                )}
                {pages && (
                    <nav
                        aria-label="Paginasi pengumuman"
                        className="mt-8 flex items-center justify-center gap-2"
                    >
                        {pengumuman.prev_page_url && (
                            <Link
                                href={pengumuman.prev_page_url}
                                className="inline-flex min-h-[44px] items-center rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700"
                            >
                                Sebelumnya
                            </Link>
                        )}
                        <span className="text-sm text-slate-500">
                            Halaman {pages.current_page} dari {pages.last_page}
                        </span>
                        {pengumuman.next_page_url && (
                            <Link
                                href={pengumuman.next_page_url}
                                className="inline-flex min-h-[44px] items-center rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700"
                            >
                                Berikutnya
                            </Link>
                        )}
                    </nav>
                )}
            </div>
        </PublicLayout>
    );
}
