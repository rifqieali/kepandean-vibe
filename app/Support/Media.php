<?php

namespace App\Support;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Http\UploadedFile;

/**
 * Satu-satunya seam kebijakan media: disk publik, batas 5 MB, visibilitas
 * publik, dan direktori per konteks. Spatie Asset (konversi thumb/preview)
 * dan FileUpload Filament duduk di seam ini sebagai adapter: keduanya
 * membaca konstanta yang sama sehingga kebijakan tidak restated per form.
 */
final class Media
{
    public const DISK = 'public';

    public const MAX_SIZE_KB = 5120;

    public static function upload(string $field, string $directory, ?string $label = null): FileUpload
    {
        $component = FileUpload::make($field)
            ->image()
            ->disk(self::DISK)
            ->maxSize(self::MAX_SIZE_KB)
            ->directory($directory)
            ->visibility(self::DISK);

        if ($label !== null) {
            $component->label($label);
        }

        return $component;
    }

    /** Editor isi dengan lampiran gambar di disk publik (default Filament = privat, tidak terjangkau browser). */
    public static function richEditor(string $field = 'isi', string $directory = 'lampiran'): RichEditor
    {
        return RichEditor::make($field)
            ->fileAttachmentsDisk(self::DISK)
            ->fileAttachmentsVisibility(self::DISK)
            ->fileAttachmentsDirectory($directory);
    }

    /** Ubah `<img data-id>` hasil RichEditor menjadi `<img src>` publik; sanitasi tetap tanggung jawab pemanggil. */
    public static function renderRich(?string $html): string
    {
        return RichContentRenderer::make((string) $html)
            ->fileAttachmentsDisk(self::DISK)
            ->fileAttachmentsVisibility(self::DISK)
            ->toHtml();
    }

    /** Kolom gambar tabel admin; disk harus eksplisit, default Filament = FILESYSTEM_DISK (privat). */
    public static function column(string $field, string $label): ImageColumn
    {
        return ImageColumn::make($field)
            ->label($label)
            ->disk(self::DISK)
            ->visibility(self::DISK);
    }

    public static function storeUploaded(UploadedFile $file, string $directory): string
    {
        $path = $file->store($directory, self::DISK);

        if ($path === false) {
            throw new \RuntimeException("Gagal menyimpan berkas ke disk '".self::DISK."'.");
        }

        return $path;
    }

    public static function url(?string $path): ?string
    {
        return $path ? asset('storage/'.$path) : null;
    }
}
