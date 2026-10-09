import { Head, Link, usePage } from '@inertiajs/react';
import {
    ChevronDown,
    Clock,
    Facebook,
    Mail,
    MapPin,
    Menu,
    Phone,
    ShieldCheck,
    X,
} from 'lucide-react';
import { useEffect, useState, type ReactNode } from 'react';
import type {
    AnalyticsData,
    MetaData,
    NavItem,
    SchemaData,
    SiteData,
} from '@/types/site';

interface PublicLayoutProps {
    meta: MetaData;
    site: SiteData;
    schema?: SchemaData | null;
    children: ReactNode;
}

function SiteLogo({
    url,
    className = 'h-10 sm:h-11',
    inisial = false,
}: {
    url: string | null;
    className?: string;
    inisial?: boolean;
}) {
    if (url) {
        return (
            <img
                src={url}
                alt=""
                className={`w-auto shrink-0 object-contain ${className}`}
            />
        );
    }

    return inisial ? (
        <span
            aria-hidden="true"
            className="flex h-10 w-10 shrink-0 items-center justify-center rounded-sm bg-charcoal-ink font-inter text-lg font-bold text-paper-white sm:h-11 sm:w-11"
        >
            K
        </span>
    ) : null;
}

function DesktopNav({ items }: { items: NavItem[] }) {
    return (
        <nav
            aria-label="Navigasi utama"
            className="hidden items-center gap-0.5 lg:flex"
        >
            {items.map((item) =>
                item.children ? (
                    <div key={item.label} className="group relative">
                        <Link
                            href={item.href}
                            className="flex min-h-[44px] items-center gap-1 rounded-sm px-1.5 py-2 font-inter-tight text-[13px] font-medium whitespace-nowrap text-charcoal-ink hover:bg-charcoal-ink/5 xl:px-2 xl:text-sm min-[1480px]:px-2.5"
                        >
                            {item.label}
                            <ChevronDown
                                className="h-4 w-4"
                                aria-hidden="true"
                            />
                        </Link>
                        <div className="invisible absolute left-0 z-50 w-56 rounded-lg bg-paper-white py-1 opacity-0 shadow-md ring-1 ring-black/5 group-focus-within:visible group-focus-within:opacity-100 group-hover:visible group-hover:opacity-100">
                            {item.children.map((child) => (
                                <Link
                                    key={child.label}
                                    href={child.href}
                                    className="block px-4 py-2 text-sm text-graphite hover:bg-studio-canvas"
                                >
                                    {child.label}
                                </Link>
                            ))}
                        </div>
                    </div>
                ) : (
                    <Link
                        key={item.label}
                        href={item.href}
                        className="flex min-h-[44px] items-center rounded-sm px-1.5 py-2 font-inter-tight text-[13px] font-medium whitespace-nowrap text-charcoal-ink hover:bg-charcoal-ink/5 xl:px-2 xl:text-sm min-[1480px]:px-2.5"
                    >
                        {item.label}
                    </Link>
                ),
            )}
        </nav>
    );
}

function MobileNav({
    items,
    siteName,
}: {
    items: NavItem[];
    siteName: string;
}) {
    const [open, setOpen] = useState(false);

    useEffect(() => {
        if (!open) {
            return;
        }
        const tutup = (e: KeyboardEvent) => {
            if (e.key === 'Escape') {
                setOpen(false);
            }
        };
        window.addEventListener('keydown', tutup);
        return () => window.removeEventListener('keydown', tutup);
    }, [open]);

    return (
        <div className="lg:hidden">
            <button
                type="button"
                onClick={() => setOpen((v) => !v)}
                aria-expanded={open}
                aria-controls="navigasi-seluler"
                aria-label={open ? 'Tutup menu navigasi' : 'Buka menu navigasi'}
                className="flex min-h-[48px] min-w-[48px] items-center justify-center rounded-sm text-charcoal-ink hover:bg-charcoal-ink/5"
            >
                {open ? (
                    <X className="h-6 w-6" aria-hidden="true" />
                ) : (
                    <Menu className="h-6 w-6" aria-hidden="true" />
                )}
            </button>
            {open && (
                <nav
                    id="navigasi-seluler"
                    aria-label={`Navigasi utama ${siteName}`}
                    className="absolute inset-x-0 top-full z-50 max-h-[calc(100dvh-5rem)] overflow-y-auto border-t border-ghost-line bg-studio-canvas px-4 py-3"
                >
                    <ul className="flex flex-col">
                        {items.map((item) => (
                            <li key={item.label}>
                                <Link
                                    href={item.href}
                                    onClick={() => setOpen(false)}
                                    className="flex min-h-[44px] items-center rounded-sm px-3 py-2.5 text-sm font-medium text-charcoal-ink hover:bg-charcoal-ink/5"
                                >
                                    {item.label}
                                </Link>
                                {item.children && (
                                    <ul className="ml-4 border-l border-ghost-line pl-2">
                                        {item.children.map((child) => (
                                            <li key={child.label}>
                                                <Link
                                                    href={child.href}
                                                    onClick={() =>
                                                        setOpen(false)
                                                    }
                                                    className="flex min-h-[44px] items-center rounded-sm px-3 py-2 text-sm text-graphite hover:bg-charcoal-ink/5"
                                                >
                                                    {child.label}
                                                </Link>
                                            </li>
                                        ))}
                                    </ul>
                                )}
                            </li>
                        ))}
                    </ul>
                </nav>
            )}
        </div>
    );
}

function Footer({ site }: { site: SiteData }) {
    const { layanan, cepat } = site.footer;

    return (
        <footer className="border-t border-ghost-line bg-studio-canvas">
            <div className="mx-auto grid w-full max-w-[1440px] gap-8 px-4 py-10 sm:grid-cols-2 sm:px-8 lg:grid-cols-4">
                <div>
                    <div className="flex items-center gap-3">
                        <SiteLogo url={site.logo_url} className="h-12" />
                        <div>
                            <p className="text-base font-bold tracking-wide text-charcoal-ink">
                                DESA KEPANDEAN
                            </p>
                            <p className="font-inter-tight text-sm text-soft-ash">
                                Dukuhturi, Kabupaten Tegal
                            </p>
                        </div>
                    </div>
                    <p className="mt-3 text-base leading-6 text-graphite">
                        Portal Digital Resmi Pemerintahan Desa Kepandean yang
                        transparan, akuntabel, dan mengutamakan pelayanan
                        masyarakat inklusif.
                    </p>
                    <ul className="mt-4 space-y-2 text-base text-graphite">
                        <li className="flex items-start gap-2">
                            <MapPin
                                className="mt-0.5 h-4 w-4 shrink-0 text-charcoal-ink"
                                aria-hidden="true"
                            />
                            <span>
                                Dukuhturi, Kab. Tegal, Jawa Tengah, Kode Pos
                                52192
                            </span>
                        </li>
                        <li className="flex items-start gap-2">
                            <Phone
                                className="mt-0.5 h-4 w-4 shrink-0 text-charcoal-ink"
                                aria-hidden="true"
                            />
                            <span>{site.kontak.telepon}</span>
                        </li>
                        <li className="flex items-start gap-2">
                            <Mail
                                className="mt-0.5 h-4 w-4 shrink-0 text-charcoal-ink"
                                aria-hidden="true"
                            />
                            <span>{site.kontak.surel}</span>
                        </li>
                    </ul>
                </div>
                <nav aria-label="Navigasi cepat">
                    <p className="border-b-2 border-charcoal-ink pb-1 text-base font-semibold">
                        Navigasi Cepat
                    </p>
                    <ul className="mt-3 space-y-2 text-base">
                        {cepat.map((l) => (
                            <li key={l.label}>
                                <Link
                                    href={l.href}
                                    className="font-inter-tight text-[15px] font-medium text-graphite hover:text-charcoal-ink hover:underline"
                                >
                                    › {l.label}
                                </Link>
                            </li>
                        ))}
                    </ul>
                </nav>
                <nav aria-label="Layanan publik">
                    <p className="border-b-2 border-charcoal-ink pb-1 text-base font-semibold">
                        Layanan Publik
                    </p>
                    <ul className="mt-3 space-y-2 text-base">
                        {layanan.map((l) => (
                            <li key={l.label}>
                                <Link
                                    href={l.href}
                                    className="font-inter-tight text-[15px] font-medium text-graphite hover:text-charcoal-ink hover:underline"
                                >
                                    › {l.label}
                                </Link>
                            </li>
                        ))}
                    </ul>
                </nav>
                <div>
                    <p className="border-b-2 border-charcoal-ink pb-1 text-base font-semibold">
                        Jam Pelayanan
                    </p>
                    <dl className="mt-3 space-y-2 rounded-xl bg-paper-white p-3 text-base">
                        {site.kontak.jam.map((j) => (
                            <div key={j.hari}>
                                <dt className="font-medium">{j.hari}:</dt>
                                <dd className="text-graphite">{j.jam}</dd>
                            </div>
                        ))}
                    </dl>
                    <p className="mt-4 text-base font-semibold">
                        Media Sosial Resmi
                    </p>
                    <ul className="mt-1 space-y-1 text-base">
                        {site.kontak.sosmed.map((s) => (
                            <li key={s.label}>
                                <a
                                    href={s.href}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="flex items-center gap-2 text-graphite hover:text-charcoal-ink hover:underline"
                                >
                                    <Facebook
                                        className="h-4 w-4"
                                        aria-hidden="true"
                                    />
                                    {s.label}
                                </a>
                            </li>
                        ))}
                    </ul>
                </div>
            </div>
            <div className="border-t border-ghost-line">
                <div className="mx-auto flex w-full max-w-[1440px] flex-col gap-1 px-4 py-3 text-[11px] leading-[1.66] tracking-[-0.11px] text-soft-ash sm:flex-row sm:items-center sm:justify-between sm:px-8">
                    <p className="flex items-center gap-1">
                        <Clock className="h-3.5 w-3.5" aria-hidden="true" />©
                        2026 Pemerintah Desa Kepandean, Kecamatan Dukuhturi,
                        Kabupaten Tegal. Hak Cipta Dilindungi Undang-Undang.
                    </p>
                </div>
            </div>
        </footer>
    );
}

export default function PublicLayout({
    meta,
    site,
    schema,
    children,
}: PublicLayoutProps) {
    const { props } = usePage<{ analytics?: AnalyticsData }>();
    const analytics = props.analytics;
    const gaId =
        typeof analytics?.ga_id === 'string' && analytics.ga_id !== ''
            ? analytics.ga_id
            : null;
    const scVerification =
        typeof analytics?.search_console_verification === 'string' &&
        analytics.search_console_verification !== ''
            ? analytics.search_console_verification
            : null;
    const ogType =
        schema !== null &&
        schema !== undefined &&
        (schema as Record<string, unknown>)['@type'] === 'NewsArticle'
            ? 'article'
            : 'website';

    return (
        <>
            <Head title={meta.title}>
                <meta name="description" content={meta.description} />
                {meta.canonical_url ? (
                    <link rel="canonical" href={meta.canonical_url} />
                ) : null}
                <meta property="og:type" content={ogType} />
                <meta property="og:title" content={meta.title} />
                <meta property="og:description" content={meta.description} />
                {meta.canonical_url ? (
                    <meta property="og:url" content={meta.canonical_url} />
                ) : null}
                {meta.og_image ? (
                    <meta property="og:image" content={meta.og_image} />
                ) : null}
                <meta name="twitter:card" content="summary_large_image" />
                <meta name="twitter:title" content={meta.title} />
                <meta name="twitter:description" content={meta.description} />
                {meta.og_image ? (
                    <meta name="twitter:image" content={meta.og_image} />
                ) : null}
                {scVerification ? (
                    <meta
                        name="google-site-verification"
                        content={scVerification}
                    />
                ) : null}
                {schema ? (
                    <script type="application/ld+json">
                        {JSON.stringify(schema)}
                    </script>
                ) : null}
                {gaId ? (
                    <script
                        async
                        src={`https://www.googletagmanager.com/gtag/js?id=${gaId}`}
                    />
                ) : null}
                {gaId ? (
                    <script>
                        {`window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','${gaId}');`}
                    </script>
                ) : null}
            </Head>
            <div className="flex min-h-screen flex-col bg-studio-canvas font-inter text-charcoal-ink">
                <header className="sticky top-0 z-[100] border-b border-ghost-line bg-studio-canvas">
                    <div className="relative mx-auto flex h-16 w-full max-w-[1440px] items-center justify-between gap-3 px-4 sm:gap-6 sm:px-6 lg:px-8">
                        <Link
                            href="/"
                            className="flex min-w-0 items-center gap-2.5 sm:gap-4"
                        >
                            <SiteLogo url={site.logo_url} inisial />
                            <span className="min-w-0 leading-tight">
                                <span className="block truncate text-[13px] font-bold tracking-normal whitespace-nowrap text-charcoal-ink sm:text-base sm:tracking-wide">
                                    <span className="xl:hidden">
                                        DESA KEPANDEAN
                                    </span>
                                    <span className="hidden xl:inline">
                                        PEMERINTAH DESA KEPANDEAN
                                    </span>
                                </span>
                                <span className="mt-0.5 block truncate font-inter-tight text-[11px] whitespace-nowrap text-soft-ash sm:text-sm">
                                    <span className="xl:hidden">
                                        Dukuhturi, Kab. Tegal
                                    </span>
                                    <span className="hidden xl:inline">
                                        Kecamatan Dukuhturi - Kabupaten Tegal
                                    </span>
                                </span>
                            </span>
                        </Link>
                        <DesktopNav items={site.nav} />
                        <div className="flex shrink-0 items-center gap-2">
                            <Link
                                href="/layanan-warga"
                                aria-label="Layanan Mandiri Warga"
                                title="Layanan Mandiri Warga"
                                className="inline-flex h-11 w-11 items-center justify-center gap-1.5 rounded-lg bg-charcoal-ink text-sm font-semibold whitespace-nowrap text-paper-white hover:shadow-md md:h-auto md:w-auto md:px-6 md:py-3 lg:max-[1479px]:h-11 lg:max-[1479px]:w-11 lg:max-[1479px]:p-0"
                            >
                                <ShieldCheck
                                    className="h-4 w-4"
                                    aria-hidden="true"
                                />
                                <span className="hidden md:inline lg:max-[1479px]:hidden">
                                    Layanan Mandiri Warga
                                </span>
                            </Link>
                            <MobileNav items={site.nav} siteName={site.nama} />
                        </div>
                    </div>
                </header>
                <main className="flex-1">{children}</main>
                <Footer site={site} />
            </div>
        </>
    );
}
