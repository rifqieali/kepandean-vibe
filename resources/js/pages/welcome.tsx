import { Link } from '@inertiajs/react';
import {
    ArrowRight,
    Bell,
    ChevronLeft,
    ChevronRight,
    FileText,
    IdCard,
    Info,
    MapPin,
    MessageCircle,
    MessageSquareWarning,
    Newspaper,
    Users,
} from 'lucide-react';
import {
    useEffect,
    useRef,
    useState,
    type KeyboardEvent as KeyboardEventReact,
    type PointerEvent as PointerEventReact,
} from 'react';
import LokasiMap from '@/components/lokasi-map';
import PublicLayout from '@/layouts/public-layout';
import type {
    BeritaTerkiniItem,
    HeroSlideItem,
    LokasiData,
    MetaData,
    ProfilExcerpt,
    SchemaData,
    SiteData,
    StatistikMap,
} from '@/types/site';

interface WelcomeProps {
    profilExcerpt: ProfilExcerpt;
    beritaTerkini: BeritaTerkiniItem[];
    heroSlides: HeroSlideItem[];
    statistik: StatistikMap;
    lokasi: LokasiData;
    site: SiteData;
    meta: MetaData;
    schema?: SchemaData | null;
}

const LAYANAN = [
    {
        ikon: FileText,
        judul: 'Surat Online',
        deskripsi: 'Pembuatan surat online cepat',
        href: '/layanan-warga',
    },
    {
        ikon: Bell,
        judul: 'Pengumuman',
        deskripsi: 'Informasi & Pengumuman',
        href: '/pengumuman',
    },
    {
        ikon: Users,
        judul: 'Kependudukan',
        deskripsi: 'Informasi & Layanan Kependudukan',
        href: '/layanan-warga',
    },
    {
        ikon: MessageSquareWarning,
        judul: 'Aduan Warga',
        deskripsi: 'Sampaikan aspirasi & keluhan anda',
        href: '#aduan',
    },
    {
        ikon: Info,
        judul: 'Informasi Publik',
        deskripsi: 'Transparansi data & Informasi desa',
        href: '/informasi',
    },
];

function Hero({
    siteName,
    slides,
    fotoUrl,
}: {
    siteName: string;
    slides: HeroSlideItem[];
    fotoUrl: string | null;
}) {
    const [indeks, setIndeks] = useState(0);
    const [jeda, setJeda] = useState(false);
    const jumlah = slides.length;

    useEffect(() => {
        if (indeks >= jumlah && jumlah > 0) {
            setIndeks(0);
        }
    }, [indeks, jumlah]);

    useEffect(() => {
        if (jumlah <= 1 || jeda) {
            return;
        }

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        const id = window.setInterval(() => {
            setIndeks((i) => (i + 1) % jumlah);
        }, 5000);

        return () => window.clearInterval(id);
    }, [jumlah, jeda]);

    if (jumlah === 0) {
        return (
            <section className="px-4 pt-14 pb-[60px] sm:px-8">
                <div className="mx-auto grid w-full max-w-[1440px] items-start gap-8 lg:grid-cols-[1.05fr_1.3fr]">
                    <div>
                        <p className="flex items-center gap-3 font-inter-tight text-[15px] font-medium">
                            <span
                                className="h-[2px] w-7 bg-charcoal-ink"
                                aria-hidden="true"
                            />
                            Selamat Datang di
                        </p>
                        <h1 className="mt-4 font-inter text-[80px] leading-[1] font-semibold tracking-[-3.2px] text-charcoal-ink max-lg:text-[64px] max-lg:tracking-[-2.56px] max-sm:text-5xl max-sm:tracking-[-1.92px]">
                            {siteName}
                        </h1>
                        <p className="mt-4 max-w-[34ch] text-[19px] leading-[1.4]">
                            Kecamatan Dukuhturi, Kabupaten Tegal
                        </p>
                        <p className="mt-4 max-w-[46ch] text-base leading-[1.66]">
                            Mengenal lebih dekat profil, informasi, pelayanan
                            dan potensi {siteName}.
                        </p>
                        <Link
                            href="#layanan"
                            className="mt-6 inline-flex min-h-[48px] items-center gap-2.5 rounded-lg bg-charcoal-ink px-6 py-3 text-base font-semibold tracking-[-0.64px] text-paper-white hover:shadow-md active:scale-[0.96] motion-reduce:transition-none"
                        >
                            Jelajahi Desa
                            <ArrowRight
                                className="h-4 w-4"
                                aria-hidden="true"
                            />
                        </Link>
                    </div>
                    <div className="relative min-h-[420px]" aria-hidden="true">
                        <div className="absolute top-0 right-[12%] w-[46%] rounded-lg border border-ghost-line bg-graphite p-16 text-paper-white">
                            <p className="text-sm text-paper-white/70">
                                Foto desa akan tampil di sini setelah galeri
                                resmi tersedia.
                            </p>
                        </div>
                        <div className="absolute top-[120px] right-0 w-[40%] rounded-sm border border-ghost-line bg-paper-white p-4">
                            <p className="text-sm text-soft-ash">
                                Pratinjau karya dan kegiatan warga.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        );
    }

    const aktif = slides[indeks] ?? slides[0];
    const sebelumnya = () => setIndeks((i) => (i - 1 + jumlah) % jumlah);
    const berikutnya = () => setIndeks((i) => (i + 1) % jumlah);

    const titikSentuh = useRef<number | null>(null);
    const geserMulai = (e: PointerEventReact) => {
        titikSentuh.current = e.clientX;
    };
    const geserSelesai = (e: PointerEventReact) => {
        if (titikSentuh.current === null) {
            return;
        }
        const beda = e.clientX - titikSentuh.current;
        titikSentuh.current = null;
        if (Math.abs(beda) < 40 || jumlah <= 1) {
            return;
        }
        if (beda < 0) {
            berikutnya();
        } else {
            sebelumnya();
        }
    };
    const tombolPanah = (e: KeyboardEventReact) => {
        if (e.key === 'ArrowLeft') {
            sebelumnya();
        } else if (e.key === 'ArrowRight') {
            berikutnya();
        }
    };

    const judulAktif =
        (aktif.judul.replace(/^Selamat\s+Datang\s+di\s+/i, '') || siteName);

    return (
        <section
            aria-roledescription="carousel"
            aria-label="Sorotan Desa Kepandean"
            onMouseEnter={() => setJeda(true)}
            onMouseLeave={() => setJeda(false)}
            onFocus={() => setJeda(true)}
            onBlur={() => setJeda(false)}
            onPointerDown={geserMulai}
            onPointerUp={geserSelesai}
            onPointerCancel={() => {
                titikSentuh.current = null;
            }}
            onKeyDown={tombolPanah}
            className="px-4 pt-14 pb-[60px] [touch-action:pan-y] sm:px-8"
        >
            <div className="mx-auto grid w-full max-w-[1440px] items-start gap-8 lg:grid-cols-[1.05fr_1.3fr]">
                <div>
                    <p className="flex items-center gap-3 font-inter-tight text-[15px] font-medium">
                        <span
                            className="h-[2px] w-7 bg-charcoal-ink"
                            aria-hidden="true"
                        />
                        Selamat Datang di
                    </p>
                    <div
                        className="flex transition-transform duration-700 ease-out motion-reduce:transition-none"
                        style={{ transform: `translateX(-${indeks * 100}%)` }}
                    >
                        {slides.map((s, i) => {
                            const isAktif = i === indeks;
                            const judul =
                                s.judul.replace(
                                    /^Selamat\s+Datang\s+di\s+/i,
                                    '',
                                ) || siteName;
                            return (
                                <div
                                    key={`${s.judul}-${i}`}
                                    aria-hidden={!isAktif}
                                    inert={!isAktif}
                                    className="w-full shrink-0"
                                >
                                    {isAktif ? (
                                        <h1 className="mt-4 font-inter text-[80px] leading-[1] font-semibold tracking-[-3.2px] text-charcoal-ink max-lg:text-[64px] max-lg:tracking-[-2.56px] max-sm:text-5xl max-sm:tracking-[-1.92px]">
                                            {judul}
                                        </h1>
                                    ) : (
                                        <h2
                                            aria-hidden="true"
                                            className="mt-4 font-inter text-[80px] leading-[1] font-semibold tracking-[-3.2px] text-charcoal-ink max-lg:text-[64px] max-lg:tracking-[-2.56px] max-sm:text-5xl max-sm:tracking-[-1.92px]"
                                        >
                                            {judul}
                                        </h2>
                                    )}
                                </div>
                            );
                        })}
                    </div>
                    {aktif.subjudul && (
                        <p className="mt-4 max-w-[34ch] text-[19px] leading-[1.4] font-medium">
                            {aktif.subjudul}
                        </p>
                    )}
                    <p className="mt-4 max-w-[46ch] text-base leading-[1.66]">
                        Mengenal lebih dekat profil, informasi, pelayanan dan
                        potensi {siteName}.
                    </p>
                    <div className="mt-6 flex flex-wrap items-center gap-3">
                        <Link
                            href={aktif.tautan_url || '#layanan'}
                            className="inline-flex min-h-[48px] items-center gap-2.5 rounded-lg bg-charcoal-ink px-6 py-3 text-base font-semibold tracking-[-0.64px] text-paper-white hover:shadow-md active:scale-[0.96] motion-reduce:transition-none"
                        >
                            {aktif.tautan_label || 'Jelajahi Desa'}
                            <ArrowRight
                                className="h-4 w-4"
                                aria-hidden="true"
                            />
                        </Link>
                    </div>
                    {jumlah > 1 && (
                        <div className="mt-6 flex items-center gap-2">
                            <button
                                type="button"
                                onClick={sebelumnya}
                                aria-label="Tampilkan slide sebelumnya"
                                className="flex min-h-[44px] min-w-[44px] items-center justify-center rounded-sm border border-charcoal-ink hover:bg-charcoal-ink/5"
                            >
                                <ChevronLeft
                                    className="h-5 w-5"
                                    aria-hidden="true"
                                />
                            </button>
                            <div
                                role="tablist"
                                aria-label="Pilih slide"
                                className="flex items-center gap-2"
                            >
                                {slides.map((s, i) => (
                                    <button
                                        key={`${s.judul}-${i}`}
                                        type="button"
                                        role="tab"
                                        aria-selected={i === indeks}
                                        aria-label={`Tampilkan slide ${i + 1}: ${s.judul}`}
                                        onClick={() => setIndeks(i)}
                                        className="group flex min-h-[44px] min-w-[44px] items-center justify-center"
                                    >
                                        <span
                                            aria-hidden="true"
                                            className={
                                                i === indeks
                                                    ? 'block h-2.5 w-6 rounded-full bg-charcoal-ink'
                                                    : 'block h-2.5 w-2.5 rounded-full bg-charcoal-ink/30 group-hover:bg-charcoal-ink/60'
                                            }
                                        />
                                    </button>
                                ))}
                            </div>
                            <button
                                type="button"
                                onClick={berikutnya}
                                aria-label="Tampilkan slide berikutnya"
                                className="flex min-h-[44px] min-w-[44px] items-center justify-center rounded-sm border border-charcoal-ink hover:bg-charcoal-ink/5"
                            >
                                <ChevronRight
                                    className="h-5 w-5"
                                    aria-hidden="true"
                                />
                            </button>
                        </div>
                    )}
                    <p aria-live="polite" className="sr-only">
                        Slide {indeks + 1} dari {jumlah}: {aktif.judul}
                    </p>
                </div>
                <div
                    className="relative min-h-[420px]"
                    aria-label="Kolase foto sorotan desa"
                >
                    {aktif.gambar_url ? (
                        <img
                            src={aktif.gambar_url}
                            alt={judulAktif}
                            loading="eager"
                            decoding="async"
                            draggable={false}
                            className="absolute top-0 right-[12%] h-64 w-[46%] rounded-lg border border-ghost-line object-cover"
                        />
                    ) : (
                        <div className="absolute top-0 right-[12%] grid h-64 w-[46%] place-items-center rounded-lg bg-graphite p-6 text-center text-sm text-paper-white/80">
                            Foto sorotan menyusul dari galeri resmi desa.
                        </div>
                    )}
                    {fotoUrl ? (
                        <img
                            src={fotoUrl}
                            alt="Suasana Desa Kepandean"
                            loading="lazy"
                            decoding="async"
                            className="absolute top-[120px] right-0 h-52 w-[40%] rounded-sm border border-ghost-line object-cover"
                        />
                    ) : (
                        <div className="absolute top-[250px] right-[22%] w-[44%] rounded-lg bg-absolute-black p-6 text-paper-white">
                            <p className="text-base font-semibold">
                                Potensi Desa
                            </p>
                            <p className="mt-1 text-sm text-paper-white/70">
                                Galeri potensi dan kegiatan warga akan tampil
                                overlapping di sini setelah dokumentasi resmi
                                tersedia.
                            </p>
                        </div>
                    )}
                </div>
            </div>
        </section>
    );
}

function LayananPublik() {
    return (
        <section
            id="layanan"
            aria-labelledby="layanan-publik"
            className="mx-auto w-full max-w-[1440px] scroll-mt-24 px-4 py-[60px] sm:px-8"
        >
            <h2
                id="layanan-publik"
                className="font-inter text-5xl font-bold tracking-[-1.92px] text-charcoal-ink max-sm:text-[32px] max-sm:tracking-[-1.28px]"
            >
                Layanan Publik
            </h2>
            <p className="mt-3 text-[19px] leading-[1.4]">
                Akses cepat untuk kebutuhan masyarakat
            </p>
            <ul className="mt-8 grid gap-4">
                {LAYANAN.map((l, i) => (
                    <li key={l.judul}>
                        <Link
                            href={l.href}
                            className={`group flex h-full items-center gap-4 rounded-lg border border-ghost-line bg-paper-white hover:shadow-md ${i === 0 ? 'p-7' : 'p-6'}`}
                        >
                            <span className="flex h-14 w-14 shrink-0 items-center justify-center rounded-sm bg-charcoal-ink text-paper-white transition-transform group-hover:scale-105">
                                <l.ikon
                                    className="h-6 w-6"
                                    aria-hidden="true"
                                />
                            </span>
                            <span className="min-w-0 flex-1">
                                <span className="block text-xl font-bold text-charcoal-ink">
                                    {l.judul}
                                </span>
                                <span className="mt-0.5 block text-sm text-soft-ash">
                                    {l.deskripsi}
                                </span>
                            </span>
                        </Link>
                    </li>
                ))}
            </ul>
        </section>
    );
}

function SekilasSejarah({
    excerpt,
    beritaTerkini,
}: {
    excerpt: ProfilExcerpt;
    beritaTerkini: BeritaTerkiniItem[];
}) {
    return (
        <section
            aria-labelledby="sekilas-sejarah"
            className="mx-auto w-full max-w-[1440px] px-4 py-[60px] sm:px-8"
        >
            <h2
                id="sekilas-sejarah"
                className="max-w-[20ch] font-inter text-5xl font-bold tracking-[-1.92px] text-charcoal-ink max-sm:text-[32px] max-sm:tracking-[-1.28px]"
            >
                Sekilas Desa Kepandean
            </h2>
            <div className="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
                <article className="overflow-hidden rounded-lg border border-ghost-line bg-paper-white lg:col-span-2">
                    <div
                        className={
                            excerpt.foto_url ? 'grid md:grid-cols-5' : undefined
                        }
                    >
                        <div
                            className={`p-6 sm:p-8 ${excerpt.foto_url ? 'md:col-span-3' : ''}`}
                        >
                            {excerpt.sejarah ? (
                                <>
                                    <p className="text-base leading-7 text-graphite">
                                        {excerpt.sejarah}
                                    </p>
                                    <Link
                                        href={excerpt.urls.sejarah}
                                        className="mt-5 inline-flex min-h-[48px] items-center gap-2 rounded-lg bg-charcoal-ink px-6 py-3 text-base font-semibold text-paper-white hover:shadow-md"
                                    >
                                        Selengkapnya
                                        <ArrowRight
                                            className="h-4 w-4"
                                            aria-hidden="true"
                                        />
                                    </Link>
                                </>
                            ) : (
                                <p className="text-base leading-7 text-soft-ash">
                                    Cuplikan sejarah desa belum tersedia.
                                    Perangkat desa akan melengkapinya setelah
                                    data dari OpenSID dikonfirmasi.{' '}
                                    <Link
                                        href={excerpt.urls.sejarah}
                                        className="font-medium text-charcoal-ink underline"
                                    >
                                        Buka halaman Sejarah
                                    </Link>
                                    .
                                </p>
                            )}
                        </div>
                        {excerpt.foto_url ? (
                            <div className="order-first aspect-[16/9] md:order-none md:col-span-2 md:aspect-auto md:min-h-64">
                                <img
                                    src={excerpt.foto_url}
                                    alt="Suasana Desa Kepandean"
                                    loading="lazy"
                                    decoding="async"
                                    className="h-full w-full rounded-sm object-cover"
                                />
                            </div>
                        ) : null}
                    </div>
                </article>
                <aside
                    aria-labelledby="berita-terkini"
                    className="rounded-lg border border-ghost-line bg-paper-white p-6"
                >
                    <div className="flex items-center justify-between">
                        <h2
                            id="berita-terkini"
                            className="flex items-center gap-2 text-xl font-bold tracking-tight"
                        >
                            <Newspaper
                                className="h-5 w-5 text-charcoal-ink"
                                aria-hidden="true"
                            />
                            Berita Terkini
                        </h2>
                        <Link
                            href="/berita"
                            className="flex min-h-[44px] items-center font-inter-tight text-sm font-medium text-charcoal-ink hover:underline"
                        >
                            Lihat semua
                        </Link>
                    </div>
                    {beritaTerkini.length > 0 ? (
                        <ul className="mt-3 space-y-3">
                            {beritaTerkini.map((item) => (
                                <li key={item.url}>
                                    <Link
                                        href={item.url}
                                        className="group flex items-center gap-3 rounded-sm bg-studio-canvas p-3 hover:shadow-md"
                                    >
                                        {item.cover_url ? (
                                            <img
                                                src={item.cover_url}
                                                alt={`Cover ${item.judul}`}
                                                className="h-14 w-20 shrink-0 rounded-sm object-cover"
                                                loading="lazy"
                                                decoding="async"
                                            />
                                        ) : (
                                            <span
                                                aria-hidden="true"
                                                className="flex h-14 w-20 shrink-0 items-center justify-center rounded-sm bg-charcoal-ink/10"
                                            >
                                                <Newspaper
                                                    className="h-6 w-6 text-charcoal-ink"
                                                    aria-hidden="true"
                                                />
                                            </span>
                                        )}
                                        <span className="min-w-0 flex-1">
                                            <span className="block truncate text-sm font-medium group-hover:underline">
                                                {item.judul}
                                            </span>
                                            <span className="mt-0.5 block text-xs text-soft-ash">
                                                {item.tanggal}
                                            </span>
                                        </span>
                                        <span
                                            aria-hidden="true"
                                            className="shrink-0 text-soft-ash group-hover:text-charcoal-ink"
                                        >
                                            ›
                                        </span>
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    ) : (
                        <p className="mt-3 rounded-sm bg-studio-canvas p-4 text-sm text-soft-ash">
                            Belum ada berita yang diterbitkan. Arsip lama tetap
                            dapat dibaca di situs sebelumnya.
                        </p>
                    )}
                </aside>
            </div>
        </section>
    );
}

function LokasiDesa({ lokasi }: { lokasi: LokasiData }) {
    return (
        <section
            aria-labelledby="lokasi-desa"
            className="px-4 py-[60px] sm:px-8"
        >
            <div className="mx-auto w-full max-w-[1440px]">
                <p className="font-inter-tight text-[13px] font-bold tracking-[0.08em] text-charcoal-ink uppercase">
                    Peta &amp; Aksesibilitas
                </p>
                <h2
                    id="lokasi-desa"
                    className="mt-3 font-inter text-5xl font-bold tracking-[-1.92px] text-charcoal-ink max-sm:text-[32px] max-sm:tracking-[-1.28px]"
                >
                    Lokasi Kantor Balai Desa Kepandean
                </h2>
                <div className="mt-8 grid gap-4 lg:grid-cols-3">
                    <div className="overflow-hidden rounded-lg bg-absolute-black text-paper-white lg:col-span-2">
                        <LokasiMap
                            latitude={lokasi.latitude}
                            longitude={lokasi.longitude}
                            petaUrl={lokasi.peta_url}
                            namaKantor="Kantor Balai Desa Kepandean"
                        />
                        <noscript>
                            <iframe
                                title="Peta lokasi Kantor Balai Desa Kepandean"
                                src={lokasi.peta_embed}
                                className="h-64 w-full rounded-sm border-0"
                                loading="lazy"
                            />
                        </noscript>
                        <div className="flex flex-wrap items-center justify-between gap-3 p-5">
                            <p className="flex items-center gap-1.5 text-sm font-medium">
                                <MapPin
                                    className="h-4 w-4 text-paper-white"
                                    aria-hidden="true"
                                />
                                Koordinat Titik: {lokasi.koordinat}
                            </p>
                            <a
                                href={lokasi.peta_url}
                                target="_blank"
                                rel="noreferrer"
                                className="inline-flex min-h-[48px] items-center gap-2 rounded-lg bg-paper-white px-6 py-3 text-sm font-semibold text-graphite hover:shadow-md"
                            >
                                Buka Peta Digital
                            </a>
                        </div>
                    </div>
                    <dl className="rounded-lg border border-ghost-line bg-paper-white p-6 text-sm">
                        <dt className="font-semibold">Detail Kantor Desa</dt>
                        <dd className="mt-3 space-y-2 text-graphite">
                            <p>
                                <span className="block text-xs text-soft-ash">
                                    Desa / Kelurahan:
                                </span>
                                Kepandean
                            </p>
                            <p>
                                <span className="block text-xs text-soft-ash">
                                    Kecamatan:
                                </span>
                                Dukuhturi
                            </p>
                            <p>
                                <span className="block text-xs text-soft-ash">
                                    Kabupaten / Provinsi:
                                </span>
                                {lokasi.alamat}
                            </p>
                            <p>
                                <span className="block text-xs text-soft-ash">
                                    Kode Pos:
                                </span>
                                {lokasi.kode_pos} (Dukuhturi)
                            </p>
                            <p>
                                <span className="block text-xs text-soft-ash">
                                    Surel Resmi:
                                </span>
                                {lokasi.surel}
                            </p>
                        </dd>
                    </dl>
                </div>
            </div>
        </section>
    );
}

// TODO: ganti nomor placeholder di bawah dengan nomor WhatsApp hotline
// resmi perangkat desa (format 628..., tanpa +, spasi, atau tanda hubung)
// sebelum rilis ke production.
const WA_HOTLINE_URL =
    'https://wa.me/6281770461804?text=Halo%20Admin%20Desa%20Kepandean%2C%20saya%20ingin%20mengajukan%20aduan.';

function HotlineAduan() {
    return (
        <section
            id="aduan"
            aria-labelledby="aduan-warga"
            className="mx-auto w-full max-w-[1440px] scroll-mt-24 px-4 py-[60px] sm:px-8"
        >
            <div className="grid items-center gap-8 rounded-lg bg-absolute-black p-6 text-paper-white sm:p-10 lg:grid-cols-12">
                <div className="lg:col-span-7">
                    <p className="font-inter-tight text-[13px] font-bold tracking-[0.08em] text-paper-white/70 uppercase">
                        Layanan Respon Cepat
                    </p>
                    <h2
                        id="aduan-warga"
                        className="mt-3 font-inter text-5xl font-bold tracking-[-1.92px] text-paper-white max-sm:text-[32px] max-sm:tracking-[-1.28px]"
                    >
                        Ada Masalah Fasilitas Umum atau Kerusakan Jalan di
                        Lingkungan Anda?
                    </h2>
                    <p className="mt-4 text-base leading-relaxed text-paper-white/85">
                        Laporkan secara mudah dengan foto lokasi melalui
                        sistem Lapor Kades Kepandean. Petugas tim lapangan
                        akan meninjau langsung ke lokasi.
                    </p>
                    <div className="mt-6 flex flex-wrap items-center gap-3">
                        <a
                            href={WA_HOTLINE_URL}
                            target="_blank"
                            rel="noreferrer"
                            className="inline-flex min-h-[48px] items-center gap-2 rounded-lg bg-paper-white px-6 py-3 text-sm font-semibold text-graphite hover:shadow-md"
                        >
                            <MessageCircle
                                className="h-4 w-4"
                                aria-hidden="true"
                            />
                            WhatsApp Hotline
                        </a>
                    </div>
                </div>
                <div className="rounded-lg bg-paper-white p-6 text-charcoal-ink lg:col-span-5">
                    <h3 className="flex items-center gap-2 font-bold text-charcoal-ink">
                        <MessageCircle
                            className="h-5 w-5 text-charcoal-ink"
                            aria-hidden="true"
                        />
                        Hotline Aduan Warga
                    </h3>
                    <ol className="mt-3 list-decimal space-y-2 pl-5 text-sm leading-relaxed text-graphite">
                        <li>
                            Tekan tombol WhatsApp di bawah untuk membuka
                            chat hotline desa.
                        </li>
                        <li>
                            Tulis aduan beserta foto dan patokan lokasi
                            (cth. RT 02 / RW 03, Gang Mawar).
                        </li>
                        <li>
                            Petugas meninjau laporan dan menindaklanjuti
                            langsung ke lokasi.
                        </li>
                    </ol>
                    <a
                        href={WA_HOTLINE_URL}
                        target="_blank"
                        rel="noreferrer"
                        className="mt-5 inline-flex min-h-[48px] w-full items-center justify-center gap-2 rounded-lg bg-charcoal-ink px-4 py-3 text-sm font-semibold text-paper-white hover:shadow-md active:scale-[0.96] motion-reduce:transition-none"
                    >
                        <MessageCircle
                            className="h-4 w-4"
                            aria-hidden="true"
                        />
                        Hubungi via WhatsApp Hotline
                    </a>
                    <p className="mt-2 text-center text-xs text-soft-ash">
                        Layanan respon cepat masyarakat desa Kepandean.
                    </p>
                </div>
            </div>
        </section>
    );
}

function StatistikRingkas({ statistik }: { statistik: StatistikMap }) {
    const total = Number(statistik.total_jiwa) || 4820;
    const laki = Number(statistik.laki_laki) || 2450;
    const perempuan = Number(statistik.perempuan) || 2370;
    const persenLaki =
        total > 0 ? Math.round((laki / total) * 1000) / 10 : 50.8;
    const persenPerempuan =
        total > 0 ? Math.round((perempuan / total) * 1000) / 10 : 49.2;
    const fmt = (n: number | string) => Number(n || 0).toLocaleString('id-ID');

    return (
        <section
            aria-labelledby="statistik-penduduk"
            className="mx-auto w-full max-w-[1440px] px-4 pb-[60px] sm:px-8"
        >
            <div className="rounded-lg border border-ghost-line bg-paper-white p-6">
                <div className="flex flex-wrap items-center justify-between gap-2">
                    <h2
                        id="statistik-penduduk"
                        className="flex items-center gap-2 text-xl font-bold tracking-tight text-charcoal-ink"
                    >
                        <IdCard
                            className="h-5 w-5 text-charcoal-ink"
                            aria-hidden="true"
                        />
                        Statistik Penduduk
                    </h2>
                    <p className="text-sm font-semibold text-graphite">
                        Total:{' '}
                        <span className="text-charcoal-ink">
                            {fmt(statistik.total_jiwa)} Jiwa
                        </span>
                    </p>
                </div>
                <div className="mt-5">
                    <div
                        className="flex h-3 w-full overflow-hidden rounded-full bg-studio-canvas"
                        role="img"
                        aria-label={`${persenLaki}% laki-laki, ${persenPerempuan}% perempuan`}
                    >
                        <span
                            className="bg-charcoal-ink transition-all"
                            style={{ width: `${persenLaki}%` }}
                        />
                        <span
                            className="bg-soft-ash transition-all"
                            style={{ width: `${persenPerempuan}%` }}
                        />
                    </div>
                    <div className="mt-2 flex justify-between text-xs font-medium text-graphite">
                        <span className="flex items-center gap-1.5">
                            <span className="h-2 w-2 rounded-full bg-charcoal-ink" />
                            Laki-laki ({fmt(statistik.laki_laki)},{' '}
                            {persenLaki}%)
                        </span>
                        <span className="flex items-center gap-1.5">
                            <span className="h-2 w-2 rounded-full bg-soft-ash" />
                            Perempuan ({fmt(statistik.perempuan)},{' '}
                            {persenPerempuan}%)
                        </span>
                    </div>
                </div>
                <dl className="mt-6 grid gap-4 text-center sm:grid-cols-3">
                    <div className="rounded-lg border border-ghost-line bg-studio-canvas p-4">
                        <dt className="text-xs font-medium text-soft-ash">
                            Kepala Keluarga
                        </dt>
                        <dd className="mt-1 text-2xl font-bold text-charcoal-ink">
                            {fmt(statistik.kepala_keluarga)} KK
                        </dd>
                    </div>
                    <div className="rounded-lg border border-ghost-line bg-studio-canvas p-4">
                        <dt className="text-xs font-medium text-soft-ash">
                            Usia Produktif
                        </dt>
                        <dd className="mt-1 text-2xl font-bold text-charcoal-ink">
                            68.4%
                        </dd>
                    </div>
                    <div className="rounded-lg border border-ghost-line bg-studio-canvas p-4">
                        <dt className="text-xs font-medium text-soft-ash">
                            Jiwa per KK (rata-rata)
                        </dt>
                        <dd className="mt-1 text-2xl font-bold text-charcoal-ink">
                            {Number(statistik.kepala_keluarga) > 0
                                ? (
                                      total / Number(statistik.kepala_keluarga)
                                  ).toLocaleString('id-ID', {
                                      maximumFractionDigits: 1,
                                  })
                                : '3.6'}{' '}
                            Jiwa
                        </dd>
                    </div>
                </dl>
            </div>
        </section>
    );
}

export default function Welcome({
    profilExcerpt,
    beritaTerkini,
    heroSlides,
    statistik,
    lokasi,
    site,
    meta,
    schema,
}: WelcomeProps) {
    return (
        <PublicLayout meta={meta} schema={schema} site={site}>
            <Hero
                siteName={site.nama}
                slides={heroSlides}
                fotoUrl={profilExcerpt.foto_url}
            />
            <LayananPublik />
            <SekilasSejarah
                excerpt={profilExcerpt}
                beritaTerkini={beritaTerkini}
            />
            <LokasiDesa lokasi={lokasi} />
            <HotlineAduan />
            <StatistikRingkas statistik={statistik} />
        </PublicLayout>
    );
}
