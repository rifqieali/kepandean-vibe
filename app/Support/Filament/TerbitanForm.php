<?php

namespace App\Support\Filament;

use App\Models\User;
use App\Support\HtmlSanitizer;
use App\Support\Media;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

/**
 * Komponen form bersama tiga arsip (Berita, Pengumuman, Kegiatan).
 * Adapter (BeritaForm dkk) hanya memilih nama tabel slug, direktori cover,
 * dan perlu-tidaknya kategori/kedaluarsa.
 */
final class TerbitanForm
{
    public static function titleField(): TextInput
    {
        return TextInput::make('judul')
            ->label('Judul')
            ->required()
            ->maxLength(200)
            ->live(onBlur: true)
            ->afterStateUpdated(function (Set $set, ?string $state): void {
                $set('slug', Str::slug((string) $state));
            });
    }

    public static function slugField(string $table): TextInput
    {
        return TextInput::make('slug')
            ->label('Slug')
            ->required()
            ->maxLength(220)
            ->helperText('Otomatis dari judul, bisa diedit. Unik per desa.')
            ->unique(
                table: $table,
                column: 'slug',
                ignoreRecord: true,
                modifyRuleUsing: function (Unique $rule, Get $get): Unique {
                    return DesaScoping::uniqueInDesa($rule, DesaScoping::desaIdForUnique($get));
                },
            );
    }

    /**
     * @param  array<string, string>  $statusOptions
     * @return array<int, mixed>
     */
    public static function statusFields(
        array $statusOptions,
        string $default,
        bool $withExpiry,
        string $expiryLabel = 'Tanggal kedaluarsa',
        string $expiryHelper = 'Opsional. Yang kedaluarsa otomatis hilang dari publik.',
    ): array {
        $fields = [
            Select::make('status')
                ->label('Status')
                ->options($statusOptions)
                ->default($default)
                ->required(),
            DateTimePicker::make('published_at')
                ->label('Tanggal terbit')
                ->helperText('Diisi otomatis saat diterbitkan bila kosong.'),
        ];

        if ($withExpiry) {
            $fields[] = DateTimePicker::make('expired_at')
                ->label($expiryLabel)
                ->helperText($expiryHelper);
        }

        return $fields;
    }

    public static function coverField(string $directory): FileUpload
    {
        return Media::upload('cover_path', $directory, 'Cover');
    }

    /**
     * Aturan simpan bersama arsip: editor boleh draft, publish butuh
     * izin publish-content; isi disanitasi; tanggal terbit diisi otomatis
     * saat publish tanpa tanggal.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function applyPublishRules(array $data, ?User $user, string $publishedValue): array
    {
        // Editor boleh simpan draft; tombol publish ditolak (aturan #13).
        if (($data['status'] ?? null) === $publishedValue) {
            abort_unless($user instanceof User, 403);
            Gate::forUser($user)->authorize('publish-content');
        }

        $data['isi'] = HtmlSanitizer::clean($data['isi'] ?? '');

        if (($data['status'] ?? null) === $publishedValue && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
