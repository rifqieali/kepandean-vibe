import { Link } from '@inertiajs/react';
import { ArrowLeft, Hammer } from 'lucide-react';

// Panel jujur "belum tersedia": dipakai halaman segera-hadir dan
// arsip kosong (berita, pengumuman, kegiatan) ketika memang nol data.
export default function SegeraHadirPanel({ label }: { label: string }) {
    return (
        <div className="rounded-lg bg-slate-100 px-6 py-16 text-center">
            <p className="text-6xl font-black tracking-tight sm:text-7xl">
                Segera Hadir
            </p>
            <h2 className="mt-4 flex items-center justify-center gap-2 text-xl font-semibold">
                <Hammer className="h-5 w-5" aria-hidden="true" />
                Halaman {label} Belum Tersedia
            </h2>
            <p className="mx-auto mt-2 max-w-xl text-neutral-600">
                Mohon maaf, modul {label} sedang disiapkan oleh perangkat desa.
                Tidak ada tautan mati di portal ini. Semua menu yang belum siap
                mengarah ke halaman ini dengan jujur.
            </p>
            <Link
                href="/"
                className="mt-6 inline-flex items-center gap-2 rounded-md bg-desa-800 px-5 py-2.5 text-sm font-medium text-white hover:bg-desa-900"
            >
                <ArrowLeft className="h-4 w-4" aria-hidden="true" />
                Kembali ke Beranda
            </Link>
        </div>
    );
}
