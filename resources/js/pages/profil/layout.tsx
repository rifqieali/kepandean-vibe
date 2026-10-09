import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import PublicLayout from '@/layouts/public-layout';
import { resolveHeroUrl } from '@/components/page-hero';
import type { MetaData, SchemaData, SiteData } from '@/types/site';
export interface ProfilData {
    sejarah: string | null;
    visi: string | null;
    misi: string | null;
    foto_url: string | null;
    isEmpty: boolean;
}

interface ProfilLayoutProps {
    meta: MetaData;
    schema?: SchemaData | null;
    heading: string;
    intro: string;
    crumb: string;
    desaName: string;
    site: SiteData;
    laman?: string | null;
    coverUrl?: string | null;
    children: ReactNode;
}

export default function ProfilLayout({
    meta,
    schema,
    heading,
    intro,
    crumb,
    desaName,
    site,
    laman,
    coverUrl,
    children,
}: ProfilLayoutProps) {
    const gambarUrl = resolveHeroUrl({
        laman,
        coverUrl,
        heroLaman: site.hero_laman,
        fallbackUrl: site.hero_fallback_url,
    });

    return (
        <PublicLayout meta={meta} schema={schema} site={site}>
            <section className="relative -mt-16 flex min-h-[340px] items-center overflow-hidden bg-desa-900 text-white sm:min-h-[400px]">
                {gambarUrl && (
                    <img
                        src={gambarUrl}
                        alt=""
                        loading="eager"
                        fetchPriority="high"
                        decoding="async"
                        className="absolute inset-0 h-full w-full object-cover"
                    />
                )}
                <div
                    aria-hidden="true"
                    className="absolute inset-0 bg-black/50"
                />
                <div className="relative mx-auto w-full max-w-[1440px] px-4 pt-24 pb-12 sm:px-8 sm:pt-28 sm:pb-16">
                    <nav
                        aria-label="Breadcrumb profil"
                        className="text-xs text-white/70"
                    >
                        <ol className="flex items-center gap-2">
                            <li>
                                <Link
                                    href="/profil/sejarah-visi-misi"
                                    className="hover:underline"
                                >
                                    Profil
                                </Link>
                            </li>
                            <li aria-hidden="true">&gt;</li>
                            <li aria-current="page" className="text-white/90">
                                {crumb}
                            </li>
                        </ol>
                    </nav>
                    <p className="mt-6 flex items-center gap-3 text-sm font-medium text-white/85">
                        <span
                            aria-hidden="true"
                            className="inline-block h-0.5 w-10 bg-white"
                        />
                        Profil Desa {desaName}
                    </p>
                    <h1 className="mt-3 max-w-3xl text-3xl font-bold tracking-tight sm:text-4xl">
                        {heading}
                    </h1>
                    <p className="mt-3 max-w-2xl text-sm leading-6 text-white/75">
                        {intro}
                    </p>
                </div>
            </section>
            <div className="mx-auto w-full max-w-[1440px] flex-1 px-4 py-10 sm:px-8 sm:py-12">
                {children}
            </div>
        </PublicLayout>
    );
}
