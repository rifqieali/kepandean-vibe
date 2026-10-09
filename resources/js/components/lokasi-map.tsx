import { useEffect, useRef } from 'react';
import 'leaflet/dist/leaflet.css';

interface LokasiMapProps {
    latitude: number;
    longitude: number;
    petaUrl: string;
    namaKantor: string;
}

/**
 * Peta interaktif Leaflet, dimuat client-only (issue #18 checklist 4).
 *
 * SSR-safe: seluruh inisialisasi Leaflet ada di dalam useEffect lewat
 * dynamic import, jadi server hanya me-render kontainer kosong + fallback
 * <noscript> di pemanggil. Tile OpenStreetMap + atribusi OSM wajib tampil;
 * klik marker/popup membuka peta eksternal (lokasi.peta_url).
 */
export default function LokasiMap({
    latitude,
    longitude,
    petaUrl,
    namaKantor,
}: LokasiMapProps) {
    const containerRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        const node = containerRef.current;

        if (!node) {
            return;
        }

        let map: import('leaflet').Map | null = null;
        let cancelled = false;

        void import('leaflet').then((L) => {
            if (cancelled || !containerRef.current) {
                return;
            }

            map = L.map(containerRef.current, {
                scrollWheelZoom: false,
            }).setView([latitude, longitude], 15);

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution:
                    '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            }).addTo(map);

            const ikon = L.divIcon({
                className: 'lokasi-marker',
                html: '<span aria-hidden="true" style="display:block;width:18px;height:18px;border-radius:9999px;background:#14532d;border:3px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.4)"></span>',
                iconSize: [18, 18],
                iconAnchor: [9, 9],
            });

            L.marker([latitude, longitude], { icon: ikon, title: namaKantor })
                .addTo(map)
                .bindPopup(
                    `<strong>${namaKantor}</strong><br><a href="${petaUrl}" target="_blank" rel="noreferrer">Buka Peta Digital</a>`,
                );
        });

        return () => {
            cancelled = true;
            map?.remove();
            map = null;
        };
    }, [latitude, longitude, petaUrl, namaKantor]);

    // relative + z-0: mengunci seluruh stacking context internal
    // Leaflet (pane/kontrol zoom yang z-index-nya tinggi) di dalam
    // kontainer ini, sehingga tidak pernah menimpa navbar (z-[100]).
    return (
        <div
            ref={containerRef}
            role="application"
            aria-label={`Peta lokasi ${namaKantor}`}
            className="relative z-0 h-64 w-full rounded-md bg-neutral-100"
        />
    );
}
