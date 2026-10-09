import { useState } from 'react';
import {
    Search,
    Users,
    Shield,
    Landmark,
    MapPin,
    Network,
    LayoutGrid,
} from 'lucide-react';
import PageHero from '@/components/page-hero';
import PublicLayout from '@/layouts/public-layout';
import type { MetaData, SchemaData, SiteData } from '@/types/site';

interface PejabatItem {
    nama: string;
    jabatan: string;
    wilayah_label: string | null;
    foto_url: string | null;
}

interface PejabatGroup {
    key: string;
    label: string;
    items: PejabatItem[];
}

interface PageProps {
    groups: PejabatGroup[];
    desa: { name: string; slug: string } | null;
    meta: MetaData;
    schema?: SchemaData | null;
    site: SiteData;
}

export default function Struktur({
    groups,
    desa,
    meta,
    schema,
    site,
}: PageProps) {
    const desaName = desa?.name ?? 'Desa Kepandean';
    const [query, setQuery] = useState('');
    const [viewMode, setViewMode] = useState<'bagan' | 'daftar'>('bagan');
    const q = query.trim().toLowerCase();

    // Flatten all items
    const allItems = groups.flatMap((g) => g.items);

    // Filtered items when searching
    const searchResults = q
        ? allItems.filter(
              (item) =>
                  item.nama.toLowerCase().includes(q) ||
                  item.jabatan.toLowerCase().includes(q) ||
                  (item.wilayah_label ?? '').toLowerCase().includes(q),
          )
        : null;

    // Helper to find officials by role/title
    const kades = allItems.find((p) =>
        p.jabatan.toLowerCase().includes('kepala desa'),
    );
    const sekdes = allItems.find((p) =>
        p.jabatan.toLowerCase().includes('sekretaris'),
    );
    const bendahara = allItems.find((p) =>
        p.jabatan.toLowerCase().includes('bendahara'),
    );

    const kaurDanKasi = allItems.filter(
        (p) =>
            (p.jabatan.toLowerCase().includes('kaur') ||
                p.jabatan.toLowerCase().includes('kasi')) &&
            !p.jabatan.toLowerCase().includes('staf'),
    );

    const staf = allItems.filter((p) =>
        p.jabatan.toLowerCase().includes('staf'),
    );

    // Wilayah (RW and RT)
    const rwList = allItems.filter((p) =>
        p.jabatan.toLowerCase().includes('ketua rw'),
    );
    const rtList = allItems.filter((p) =>
        p.jabatan.toLowerCase().includes('ketua rt'),
    );

    return (
        <PublicLayout meta={meta} schema={schema} site={site}>
            <PageHero
                eyebrow={`Profil ${desaName}`}
                title={`Struktur Organisasi ${desaName}`}
                site={site}
                laman="struktur"
            />

            <div className="mx-auto w-full max-w-[1440px] flex-1 px-4 py-10 sm:px-8">
                {/* 3 Blok Kategori Ringkasan Sesuai Figma */}
                <div className="mb-12 grid gap-6 md:grid-cols-3">
                    <div className="rounded-xl border border-neutral-200/80 bg-white p-6 shadow-sm">
                        <div className="flex items-center gap-3">
                            <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-desa-800 text-white">
                                <Shield className="h-5 w-5" />
                            </span>
                            <div>
                                <h3 className="text-lg font-bold text-neutral-900">
                                    Pemerintahan Desa
                                </h3>
                                <div className="mt-1 h-0.5 w-12 bg-desa-800" />
                            </div>
                        </div>
                        <p className="mt-3 text-sm leading-relaxed text-neutral-600">
                            Pimpinan dan koordinator utama dalam penyelenggaraan
                            pemerintahan desa.
                        </p>
                    </div>

                    <div className="rounded-xl border border-neutral-200/80 bg-white p-6 shadow-sm">
                        <div className="flex items-center gap-3">
                            <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-desa-800 text-white">
                                <Landmark className="h-5 w-5" />
                            </span>
                            <div>
                                <h3 className="text-lg font-bold text-neutral-900">
                                    Perangkat Desa
                                </h3>
                                <div className="mt-1 h-0.5 w-12 bg-desa-800" />
                            </div>
                        </div>
                        <p className="mt-3 text-sm leading-relaxed text-neutral-600">
                            Pelaksanaan tugas teknis pemerintahan desa,
                            ketatausahaan, dan pelayanan masyarakat.
                        </p>
                    </div>

                    <div className="rounded-xl border border-neutral-200/80 bg-white p-6 shadow-sm">
                        <div className="flex items-center gap-3">
                            <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-desa-800 text-white">
                                <MapPin className="h-5 w-5" />
                            </span>
                            <div>
                                <h3 className="text-lg font-bold text-neutral-900">
                                    Wilayah Desa
                                </h3>
                                <div className="mt-1 h-0.5 w-12 bg-desa-800" />
                            </div>
                        </div>
                        <p className="mt-3 text-sm leading-relaxed text-neutral-600">
                            Membawahi wilayah administrasi desa yang terdiri
                            dari para Ketua RW dan Ketua RT.
                        </p>
                    </div>
                </div>

                {/* Filter & View Mode Controls */}
                <div className="mb-8 flex flex-col items-stretch justify-between gap-4 sm:flex-row sm:items-center">
                    <div className="relative w-full max-w-md">
                        <Search className="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-neutral-400" />
                        <input
                            type="search"
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder="Cari nama pejabat, jabatan, atau wilayah (mis. RW 05)…"
                            aria-label="Cari aparatur atau pejabat desa"
                            className="w-full rounded-xl border border-neutral-200/80 bg-white py-2.5 pr-4 pl-10 text-sm shadow-sm transition-all outline-none focus:border-desa-800 focus:ring-1 focus:ring-desa-800"
                        />
                    </div>

                    {!q && (
                        <div className="flex items-center gap-1 self-start rounded-xl border border-neutral-200/80 bg-white p-1 shadow-sm">
                            <button
                                type="button"
                                onClick={() => setViewMode('bagan')}
                                className={`flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition-all ${
                                    viewMode === 'bagan'
                                        ? 'bg-desa-800 text-white shadow-sm'
                                        : 'text-neutral-600 hover:bg-neutral-50 hover:text-neutral-900'
                                }`}
                            >
                                <Network className="h-3.5 w-3.5" />
                                Bagan Visual
                            </button>
                            <button
                                type="button"
                                onClick={() => setViewMode('daftar')}
                                className={`flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition-all ${
                                    viewMode === 'daftar'
                                        ? 'bg-desa-800 text-white shadow-sm'
                                        : 'text-neutral-600 hover:bg-neutral-50 hover:text-neutral-900'
                                }`}
                            >
                                <LayoutGrid className="h-3.5 w-3.5" />
                                Daftar Lengkap
                            </button>
                        </div>
                    )}
                </div>

                {/* Search Results Display */}
                {searchResults !== null ? (
                    <div>
                        <p className="mb-4 text-sm font-medium text-neutral-500">
                            Ditemukan {searchResults.length} hasil untuk
                            pencarian "{query}":
                        </p>
                        {searchResults.length > 0 ? (
                            <ul className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                {searchResults.map((item) => (
                                    <PejabatCard
                                        key={`${item.nama}-${item.jabatan}`}
                                        item={item}
                                    />
                                ))}
                            </ul>
                        ) : (
                            <div className="rounded-xl border border-neutral-200 bg-white p-10 text-center text-neutral-500">
                                Tidak ada aparatur atau pejabat desa yang cocok
                                dengan kata kunci pencarian.
                            </div>
                        )}
                    </div>
                ) : viewMode === 'bagan' ? (
                    /* Bagan Visual Hierarki Sesuai Desain Figma */
                    <div className="space-y-12">
                        {/* Tingkat 1: Kepala Desa */}
                        {kades && (
                            <div className="flex flex-col items-center">
                                <div className="mb-3 text-center">
                                    <span className="text-xs font-bold tracking-widest text-desa-800 uppercase">
                                        Pimpinan Utama
                                    </span>
                                </div>
                                <div className="w-full max-w-sm rounded-2xl border-2 border-desa-800/40 bg-white p-6 text-center shadow-md transition-all hover:shadow-lg">
                                    <div className="border-desa-100 mx-auto mb-4 flex h-20 w-20 items-center justify-center overflow-hidden rounded-full border-4 bg-desa-800 text-white shadow-sm">
                                        {kades.foto_url ? (
                                            <img
                                                src={kades.foto_url}
                                                alt={kades.nama}
                                                className="h-full w-full object-cover"
                                            />
                                        ) : (
                                            <Users className="h-10 w-10 text-white/90" />
                                        )}
                                    </div>
                                    <span className="inline-block rounded-full bg-desa-800/10 px-3 py-1 text-xs font-semibold text-desa-800">
                                        {kades.jabatan}
                                    </span>
                                    <h4 className="mt-2 text-xl font-bold text-neutral-900">
                                        {kades.nama}
                                    </h4>
                                    <p className="mt-1 text-xs text-neutral-500">
                                        Penyelenggara Utama Pemerintahan Desa
                                    </p>
                                </div>
                                <div
                                    className="h-8 w-0.5 bg-neutral-300"
                                    aria-hidden="true"
                                />
                            </div>
                        )}

                        {/* Tingkat 2: Sekretariat & Keuangan */}
                        <div className="flex flex-col items-center">
                            <span className="mb-3 text-xs font-bold tracking-widest text-neutral-500 uppercase">
                                Sekretariat Desa &amp; Keuangan
                            </span>
                            <div className="flex w-full max-w-2xl flex-wrap justify-center gap-6">
                                {sekdes && (
                                    <div className="w-full rounded-xl border border-neutral-200/80 bg-white p-5 text-center shadow-sm transition-all hover:shadow-md sm:w-72">
                                        <span className="inline-block rounded-full bg-neutral-100 px-3 py-1 text-xs font-semibold text-neutral-700">
                                            {sekdes.jabatan}
                                        </span>
                                        <h4 className="mt-2 text-lg font-bold text-neutral-900">
                                            {sekdes.nama}
                                        </h4>
                                    </div>
                                )}
                                {bendahara && (
                                    <div className="w-full rounded-xl border border-neutral-200/80 bg-white p-5 text-center shadow-sm transition-all hover:shadow-md sm:w-72">
                                        <span className="inline-block rounded-full bg-neutral-100 px-3 py-1 text-xs font-semibold text-neutral-700">
                                            {bendahara.jabatan}
                                        </span>
                                        <h4 className="mt-2 text-lg font-bold text-neutral-900">
                                            {bendahara.nama}
                                        </h4>
                                    </div>
                                )}
                            </div>
                            <div
                                className="h-8 w-0.5 bg-neutral-300"
                                aria-hidden="true"
                            />
                        </div>

                        {/* Tingkat 3: Kepala Urusan (Kaur) & Kepala Seksi (Kasi) */}
                        <div className="flex flex-col items-center">
                            <span className="mb-4 text-xs font-bold tracking-widest text-neutral-500 uppercase">
                                Pelaksana Teknis &amp; Kepala Urusan
                            </span>
                            <div className="grid w-full gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                                {kaurDanKasi.map((item) => (
                                    <div
                                        key={`${item.nama}-${item.jabatan}`}
                                        className="flex flex-col items-center justify-between rounded-xl border border-neutral-200/80 bg-white p-4 text-center shadow-sm transition-all hover:border-desa-800/40 hover:shadow-md"
                                    >
                                        <div className="border-desa-100 mb-2 flex h-14 w-14 items-center justify-center overflow-hidden rounded-full border bg-desa-50 text-desa-800">
                                            {item.foto_url ? (
                                                <img
                                                    src={item.foto_url}
                                                    alt={item.nama}
                                                    className="h-full w-full object-cover"
                                                />
                                            ) : (
                                                <span className="text-base font-bold">
                                                    {item.nama.charAt(0)}
                                                </span>
                                            )}
                                        </div>
                                        <div>
                                            <span className="line-clamp-1 block text-xs font-semibold text-desa-800">
                                                {item.jabatan}
                                            </span>
                                            <h5 className="mt-1 text-sm font-bold text-neutral-900">
                                                {item.nama}
                                            </h5>
                                        </div>
                                    </div>
                                ))}
                            </div>
                            {staf.length > 0 && (
                                <div
                                    className="h-8 w-0.5 bg-neutral-300"
                                    aria-hidden="true"
                                />
                            )}
                        </div>

                        {/* Tingkat 4: Staf Desa */}
                        {staf.length > 0 && (
                            <div className="flex flex-col items-center">
                                <span className="mb-3 text-xs font-bold tracking-widest text-neutral-500 uppercase">
                                    Staf Pendukung Pelayanan
                                </span>
                                <div className="flex w-full max-w-xl flex-wrap justify-center gap-4">
                                    {staf.map((item) => (
                                        <div
                                            key={`${item.nama}-${item.jabatan}`}
                                            className="w-full rounded-xl border border-neutral-200/80 bg-white p-4 text-center shadow-sm sm:w-64"
                                        >
                                            <span className="block text-xs font-medium text-neutral-500">
                                                {item.jabatan}
                                            </span>
                                            <h5 className="mt-1 text-sm font-bold text-neutral-900">
                                                {item.nama}
                                            </h5>
                                        </div>
                                    ))}
                                </div>
                                <div
                                    className="h-8 w-0.5 bg-neutral-300"
                                    aria-hidden="true"
                                />
                            </div>
                        )}

                        {/* Tingkat 5: Wilayah Administrasi (RW & RT) Sesuai Roster Figma */}
                        <div>
                            <div className="mb-6 text-center">
                                <span className="text-xs font-bold tracking-widest text-desa-800 uppercase">
                                    Kepala Wilayah &amp; Rukun Tetangga (RW /
                                    RT)
                                </span>
                                <h3 className="mt-1 text-xl font-bold text-neutral-900">
                                    Pemerintahan Kewilayahan Desa Kepandean
                                </h3>
                            </div>

                            <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                                {rwList.map((rw) => {
                                    // Match RTs that belong to this RW
                                    const matchingRts = rtList.filter((rt) =>
                                        rt.wilayah_label?.includes(
                                            rw.wilayah_label ?? rw.nama,
                                        ),
                                    );

                                    return (
                                        <div
                                            key={rw.nama}
                                            className="flex flex-col justify-between rounded-2xl border border-neutral-200/90 bg-white p-5 shadow-sm transition-all hover:shadow-md"
                                        >
                                            <div>
                                                <div className="flex items-center gap-3 border-b border-neutral-100 pb-3">
                                                    <div className="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-desa-800 font-bold text-white">
                                                        {rw.foto_url ? (
                                                            <img
                                                                src={
                                                                    rw.foto_url
                                                                }
                                                                alt={rw.nama}
                                                                className="h-full w-full object-cover"
                                                            />
                                                        ) : (
                                                            <span>
                                                                {rw.nama.charAt(
                                                                    0,
                                                                )}
                                                            </span>
                                                        )}
                                                    </div>
                                                    <div>
                                                        <span className="inline-block rounded bg-desa-50 px-2 py-0.5 text-xs font-bold text-desa-800">
                                                            {rw.jabatan}
                                                        </span>
                                                        <h4 className="mt-0.5 text-sm font-bold text-neutral-900">
                                                            {rw.nama}
                                                        </h4>
                                                    </div>
                                                </div>

                                                {/* RT list in this RW */}
                                                <div className="mt-4 space-y-2">
                                                    <p className="text-xs font-semibold tracking-wider text-neutral-400 uppercase">
                                                        Rukun Tetangga:
                                                    </p>
                                                    {matchingRts.length > 0 ? (
                                                        matchingRts.map(
                                                            (rt) => (
                                                                <div
                                                                    key={`${rt.nama}-${rt.wilayah_label}`}
                                                                    className="flex items-center justify-between rounded-lg bg-neutral-50 px-3 py-2 text-xs"
                                                                >
                                                                    <span className="font-semibold text-neutral-700">
                                                                        {
                                                                            rt.nama
                                                                        }
                                                                    </span>
                                                                    <span className="rounded border border-neutral-200/60 bg-white px-2 py-0.5 font-medium text-neutral-500">
                                                                        {
                                                                            rt.wilayah_label
                                                                        }
                                                                    </span>
                                                                </div>
                                                            ),
                                                        )
                                                    ) : (
                                                        <p className="text-xs text-neutral-400 italic">
                                                            Data RT sedang
                                                            dikonfirmasi
                                                        </p>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    </div>
                ) : (
                    /* Tampilan Grid Berdasarkan Kelompok Data */
                    <div className="space-y-10">
                        {groups.map((group) => (
                            <section
                                key={group.key}
                                aria-labelledby={`kelompok-${group.key}`}
                            >
                                <div className="mb-4 flex items-center gap-2">
                                    <span
                                        className="h-4 w-1 rounded-full bg-desa-800"
                                        aria-hidden="true"
                                    />
                                    <h3
                                        id={`kelompok-${group.key}`}
                                        className="text-xl font-bold text-neutral-900"
                                    >
                                        {group.label}
                                    </h3>
                                </div>
                                <ul className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    {group.items.map((item) => (
                                        <PejabatCard
                                            key={`${item.nama}-${item.jabatan}`}
                                            item={item}
                                        />
                                    ))}
                                </ul>
                            </section>
                        ))}
                    </div>
                )}
            </div>
        </PublicLayout>
    );
}

function PejabatCard({ item }: { item: PejabatItem }) {
    return (
        <li className="flex min-h-[96px] items-center gap-4 rounded-xl border border-neutral-200/80 bg-white p-4 shadow-sm transition-all hover:border-desa-800/40 hover:shadow-md">
            {item.foto_url ? (
                <img
                    src={item.foto_url}
                    alt={`Foto ${item.nama}`}
                    className="h-14 w-14 shrink-0 rounded-full border border-neutral-100 object-cover"
                    loading="lazy"
                    decoding="async"
                />
            ) : (
                <span
                    aria-hidden="true"
                    className="border-desa-100 flex h-14 w-14 shrink-0 items-center justify-center rounded-full border bg-desa-50 text-lg font-bold text-desa-800"
                >
                    {item.nama.charAt(0)}
                </span>
            )}
            <div className="min-w-0 flex-1">
                <p className="truncate font-bold text-neutral-900">
                    {item.nama}
                </p>
                <p className="truncate text-sm text-neutral-600">
                    {item.jabatan}
                </p>
                {item.wilayah_label && (
                    <span className="mt-1 inline-block rounded bg-neutral-100 px-2 py-0.5 text-xs font-medium text-neutral-600">
                        {item.wilayah_label}
                    </span>
                )}
            </div>
        </li>
    );
}
