<?php

namespace App\Support\Filament;

use App\Models\Desa;
use App\Models\Kategori;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;

/**
 * Satu-satunya seam scoping desa untuk panel Filament (deep module).
 *
 * Enam resource + KelolaProfil sebelumnya mengulang triple yang sama
 * (getEloquentQuery + visible techade + mutate desa_id) dengan tiga
 * semantik kunci yang berbeda; bocor satu tempat = bocor lintas-desa.
 * Pemanggil hanya perlu interface kecil ini — bukan auth()->user()
 * + isTechade() + where desa_id yang dihafal di tiap file.
 */
final class DesaScoping
{
    public static function currentUser(): ?User
    {
        $user = auth()->user();

        return $user instanceof User ? $user : null;
    }

    public static function canSeeAllDesa(?User $user = null): bool
    {
        $user ??= self::currentUser();

        return $user instanceof User && $user->isTechade();
    }

    public static function isTechadeContext(): bool
    {
        return self::canSeeAllDesa();
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public static function scopeForAdmin(Builder $query, ?User $user = null): Builder
    {
        $user ??= self::currentUser();

        if (! $user instanceof User) {
            return $query->whereRaw('1 = 0');
        }

        // Techade: akses semua desa, tanpa scope desa_id.
        if ($user->isTechade()) {
            return $query;
        }

        if ($user->desa_id === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('desa_id', $user->desa_id);
    }

    /** @return array<int, string> */
    public static function desaOptions(): array
    {
        /** @var array<int, string> */
        return Desa::query()->orderBy('name')->pluck('name', 'id')->all();
    }

    public static function desaSelect(?string $helperText = null): Select
    {
        return Select::make('desa_id')
            ->label('Desa')
            ->options(fn (): array => self::desaOptions())
            ->required()
            ->live()
            ->searchable()
            ->helperText($helperText ?? 'Techade wajib memilih desa; admin desa dikunci ke desanya otomatis.')
            ->visible(fn (): bool => self::isTechadeContext());
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function resolveDesaIdForCreate(array $data, ?User $user = null): array
    {
        $user ??= self::currentUser();

        abort_unless($user instanceof User, 403);

        // Techade wajib memilih desa di form; selain itu dikunci ke desa sendiri.
        if ($user->isTechade()) {
            abort_unless(filled($data['desa_id'] ?? null), 422);

            return $data;
        }

        abort_unless($user->desa_id !== null, 403);

        $data['desa_id'] = $user->desa_id;

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function lockDesaIdForSave(array $data, ?User $user = null): array
    {
        $user ??= self::currentUser();

        abort_unless($user instanceof User, 403);

        // Desa dikunci: non-techade tidak bisa memindahkan baris antar desa.
        // Overwrite eksplisit (bukan unset diam-diam) agar satu semantik
        // untuk semua halaman edit.
        if (! $user->isTechade()) {
            abort_unless($user->desa_id !== null, 403);

            $data['desa_id'] = $user->desa_id;
        }

        return $data;
    }

    /**
     * Desa untuk klausa Unique per-desa: admin/editor dikunci ke desanya,
     * techade memakai desa yang dipilih di form (reaktif via $get).
     */
    public static function desaIdForUnique(?Get $get = null, ?User $user = null): mixed
    {
        $user ??= self::currentUser();

        if ($user instanceof User && ! $user->isTechade()) {
            return $user->desa_id;
        }

        if ($get instanceof Get && filled($picked = $get('desa_id'))) {
            return $picked;
        }

        return request()->input('data.desa_id')
            ?? request()->input('desa_id');
    }

    public static function uniqueInDesa(Unique $rule, mixed $desaId): Unique
    {
        return $desaId === null ? $rule : $rule->where('desa_id', $desaId);
    }

    /** @return array<int, string> */
    public static function kategoriOptions(?User $user = null, mixed $selectedDesaId = null): array
    {
        $user ??= self::currentUser();

        $query = Kategori::query()->orderBy('nama');

        $desaId = $user instanceof User && ! $user->isTechade()
            ? $user->desa_id
            : $selectedDesaId;

        if ($desaId !== null && $desaId !== '') {
            $query->where('desa_id', $desaId);
        }

        /** @var array<int, string> */
        return $query->pluck('nama', 'id')->all();
    }

    public static function sameDesa(?User $user, mixed $recordDesaId): bool
    {
        return $user instanceof User
            && $user->desa_id !== null
            && (int) $user->desa_id === (int) $recordDesaId;
    }
}
