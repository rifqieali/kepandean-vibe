import { FileText, Landmark, Target } from 'lucide-react';
import ProfilLayout, { type ProfilData } from './layout';
import SectionHeading from './section-heading';
import type { MetaData, SchemaData, SiteData } from '@/types/site';

interface PageProps {
    profil: ProfilData;
    desa: { name: string; slug: string } | null;
    meta: MetaData;
    schema?: SchemaData | null;
    site: SiteData;
}

export default function SejarahVisiMisi({
    profil,
    desa,
    meta,
    schema,
    site,
}: PageProps) {
    const desaName = desa?.name ?? 'Desa';

    return (
        <ProfilLayout
            meta={meta}
            schema={schema}
            heading={`Sejarah & Visi Misi ${desaName}`}
            intro={`Mengenal lebih dekat sejarah ${desaName} serta arah pembangunan yang menjadi tujuan bersama masyarakat ${desaName.toLowerCase()}.`}
            crumb="Sejarah & Visi Misi"
            desaName={desaName}
            site={site}
            laman="profil"
            coverUrl={profil.foto_url}
        >
            <section
                id="sejarah"
                aria-labelledby="sejarah-heading"
                className="scroll-mt-24"
            >
                <SectionHeading>{`Sejarah ${desaName}`}</SectionHeading>
                <div className="mt-6 grid gap-8 lg:grid-cols-2">
                    <figure className="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-sm">
                        {profil.foto_url ? (
                            <img
                                src={profil.foto_url}
                                alt={`Foto sejarah ${desaName}`}
                                className="aspect-[4/3] w-full object-cover"
                                loading="lazy"
                                decoding="async"
                            />
                        ) : (
                            <div className="flex aspect-[4/3] w-full flex-col items-center justify-center bg-gradient-to-br from-desa-900 to-desa-800 p-8 text-center text-white">
                                <Landmark
                                    className="h-12 w-12 text-white/80"
                                    aria-hidden="true"
                                />
                                <figcaption className="mt-4 text-sm leading-6 text-white/80">
                                    Foto gerbang {desaName} menyusul. Perangkat
                                    desa akan mengunggah dokumentasi resmi
                                    setelah data dari OpenSID dikonfirmasi.
                                </figcaption>
                            </div>
                        )}
                    </figure>
                    <div>
                        {profil.sejarah ? (
                            <article
                                className="max-w-none text-justify text-[15px] leading-7 text-neutral-800 [&_figure]:mb-4 [&_img]:mb-4 [&_img]:w-full [&_img]:rounded-md [&_ol]:mb-4 [&_ol]:list-decimal [&_ol]:pl-6 [&_p]:mb-4 [&_ul]:mb-4 [&_ul]:list-disc [&_ul]:pl-6"
                                dangerouslySetInnerHTML={{
                                    __html: profil.sejarah,
                                }}
                            />
                        ) : (
                            <p className="rounded-lg border border-dashed border-neutral-300 bg-neutral-50 p-6 text-[15px] leading-7 text-neutral-500">
                                Konten sejarah {desaName} belum tersedia.
                                Perangkat desa akan melengkapinya setelah data
                                dari OpenSID dikonfirmasi.
                            </p>
                        )}
                    </div>
                </div>
            </section>

            <section
                id="visi-misi"
                aria-labelledby="visi-misi-heading"
                className="-mx-4 mt-10 scroll-mt-24 bg-slate-50 px-4 py-10 sm:-mx-6 sm:px-6"
            >
                <div id="visi-misi-heading">
                    <SectionHeading>{`Visi & Misi ${desaName}`}</SectionHeading>
                </div>
                <div className="mt-6 grid gap-6 lg:grid-cols-2">
                    <article
                        aria-labelledby="visi-heading"
                        className="rounded-lg border border-neutral-200 bg-white p-6 shadow-sm"
                    >
                        <div className="flex items-center gap-4">
                            <span className="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-slate-500 text-white">
                                <Target
                                    className="h-7 w-7"
                                    aria-hidden="true"
                                />
                            </span>
                            <h3
                                id="visi-heading"
                                className="text-2xl font-bold tracking-tight"
                            >
                                Visi
                            </h3>
                        </div>
                        {profil.visi ? (
                            <blockquote className="mt-5 rounded-lg bg-indigo-50 p-5 text-center text-[15px] leading-7 text-neutral-800">
                                <div
                                    className="[&_p]:mb-2 [&_p:last-child]:mb-0"
                                    dangerouslySetInnerHTML={{
                                        __html: profil.visi,
                                    }}
                                />
                            </blockquote>
                        ) : (
                            <p className="mt-5 rounded-lg border border-dashed border-neutral-300 bg-neutral-50 p-5 text-center text-[15px] leading-7 text-neutral-500">
                                Visi {desaName} belum tersedia. Perangkat desa
                                akan melengkapinya setelah data dari OpenSID
                                dikonfirmasi.
                            </p>
                        )}
                    </article>
                    <article
                        aria-labelledby="misi-heading"
                        className="rounded-lg border border-neutral-200 bg-white p-6 shadow-sm"
                    >
                        <div className="flex items-center gap-4">
                            <span className="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-slate-500 text-white">
                                <FileText
                                    className="h-7 w-7"
                                    aria-hidden="true"
                                />
                            </span>
                            <h3
                                id="misi-heading"
                                className="text-2xl font-bold tracking-tight"
                            >
                                Misi
                            </h3>
                        </div>
                        {profil.misi ? (
                            <div
                                className="mt-5 space-y-3 text-[15px] leading-7 text-neutral-800 [&_ol]:mb-4 [&_ol]:list-decimal [&_ol]:pl-6 [&_p]:mb-3 [&_ul]:mb-4 [&_ul]:list-disc [&_ul]:pl-6"
                                dangerouslySetInnerHTML={{
                                    __html: profil.misi,
                                }}
                            />
                        ) : (
                            <p className="mt-5 rounded-lg border border-dashed border-neutral-300 bg-neutral-50 p-5 text-[15px] leading-7 text-neutral-500">
                                Misi {desaName} belum tersedia. Perangkat desa
                                akan melengkapinya setelah data dari OpenSID
                                dikonfirmasi.
                            </p>
                        )}
                    </article>
                </div>
            </section>
        </ProfilLayout>
    );
}
