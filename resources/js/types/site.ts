export interface NavChild {
    label: string;
    href: string;
}

export interface NavItem {
    label: string;
    href: string;
    children?: NavChild[];
}

export interface KontakJam {
    hari: string;
    jam: string;
}

export interface FooterLink {
    label: string;
    href: string;
}

export interface SiteFooter {
    layanan: FooterLink[];
    cepat: FooterLink[];
}

export interface Sosmed {
    label: string;
    href: string;
}

export interface SiteKontak {
    telepon: string;
    telepon_status: string;
    surel: string;
    jam: KontakJam[];
    sosmed: Sosmed[];
}

export interface SiteData {
    nama: string;
    nav: NavItem[];
    footer: SiteFooter;
    kontak: SiteKontak;
    logo_url: string | null;
    favicon_url: string | null;
    hero_fallback_url: string | null;
    hero_laman: Record<string, string>;
}

export interface LokasiData {
    kode_pos: string;
    koordinat: string;
    latitude: number;
    longitude: number;
    alamat: string;
    peta_url: string;
    peta_embed: string;
    surel: string;
}

export interface StatistikMap {
    total_jiwa: string;
    laki_laki: string;
    perempuan: string;
    kepala_keluarga: string;
    [key: string]: string;
}

export interface BeritaTerkiniItem {
    judul: string;
    tanggal: string;
    cover_url: string | null;
    url: string;
}

export interface HeroSlideItem {
    judul: string;
    subjudul: string | null;
    gambar_url: string | null;
    tautan_label: string | null;
    tautan_url: string | null;
}

export interface MetaData {
    title: string;
    description: string;
    canonical_url?: string | null;
    og_image?: string | null;
}

export interface AnalyticsData {
    ga_id?: string | null;
    search_console_verification?: string | null;
}

export type SchemaData = Record<string, unknown>;

export interface ProfilExcerpt {
    sejarah: string | null;
    foto_url: string | null;
    urls: {
        sejarah: string;
        visiMisi: string;
    };
}
