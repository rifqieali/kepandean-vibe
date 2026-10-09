<?php

namespace App\Filament\Pages;

use App\Models\Desa;
use App\Models\Profil;
use App\Models\User;
use App\Support\Filament\DesaScoping;
use App\Support\HtmlSanitizer;
use App\Support\Media;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Throwable;

/**
 * @property-read Schema $form
 */
class KelolaProfil extends Page
{
    use Concerns\CanUseDatabaseTransactions;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public ?Profil $profil = null;

    protected static ?string $slug = 'profil';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    public static function getLabel(): string
    {
        return 'Profil Desa';
    }

    public function getTitle(): string|Htmlable
    {
        return static::getLabel();
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->canPublish();
    }

    public function mount(): void
    {
        $user = auth()->user();

        abort_unless($user instanceof User && $user->canPublish(), 403);

        // Developer lintas-desa: pakai desanya bila ada, kalau tidak pakai
        // desa default/pertama. Tanpa desa sama sekali = salah konfigurasi.
        $desa = $user->isDeveloper()
            ? $user->desa()->first() ?? Desa::getDefault() ?? Desa::orderBy('id')->first()
            : $user->desa()->first();

        abort_unless($desa instanceof Desa, 404);

        $this->profil = Profil::forDesa($desa);

        $this->fillForm();
    }

    /**
     * Developer berpindah antar desa tanpa ganti akun: muat ulang profil
     * desa tujuan ke form. Non-developer ditolak (403).
     */
    public function switchDesa(int $desaId): void
    {
        $user = auth()->user();

        abort_unless($user instanceof User && $user->isDeveloper(), 403);

        if ($this->profil instanceof Profil && $this->profil->desa_id === $desaId) {
            return;
        }

        $desa = Desa::find($desaId);

        abort_unless($desa instanceof Desa, 404);

        $this->profil = Profil::forDesa($desa);

        $this->fillForm();
    }

    protected function fillForm(): void
    {
        $raw = $this->profil->attributesToArray();

        // Form memakai teks biasa: HTML tersimpan dibuka jadi teks.
        $data = [
            'sejarah' => HtmlSanitizer::toPlainText($raw['sejarah'] ?? null),
            'visi' => HtmlSanitizer::toPlainText($raw['visi'] ?? null),
            'misi' => HtmlSanitizer::fromRichOrPlain($raw['misi'] ?? null),
            'foto_path' => $raw['foto_path'] ?? null,
            'logo_path' => $raw['logo_path'] ?? null,
        ];

        if (auth()->user() instanceof User && auth()->user()->isDeveloper()) {
            $data['desa_id'] = $this->profil->desa_id;
        }

        $this->callHook('beforeFill');

        $this->form->fill($data);

        $this->callHook('afterFill');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Input teks biasa dibungkus jadi paragraf HTML tersanitasi.
        foreach (['sejarah', 'visi'] as $field) {
            $value = $data[$field] ?? null;
            $data[$field] = is_string($value) ? HtmlSanitizer::fromPlainText($value) : '';
        }

        // Misi memakai editor numbering/bullet: HTML-nya disanitasi, bukan di-escape.
        $data['misi'] = HtmlSanitizer::fromRichOrPlain(is_string($data['misi'] ?? null) ? $data['misi'] : null);

        unset($data['desa_id']);

        return $data;
    }

    public function save(): void
    {
        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            // Developer menyimpan ke desa yang dipilih; selain itu dikunci
            // ke desa sendiri (desa_id di form tidak dipercaya).
            $user = auth()->user();

            abort_unless($user instanceof User && $user->canPublish(), 403);

            $target = $user->isDeveloper()
                ? Desa::find($data['desa_id'] ?? null)
                : $user->desa()->first();

            abort_unless($target instanceof Desa, 404);

            $this->profil = Profil::forDesa($target);

            $data = $this->mutateFormDataBeforeSave($data);

            $this->callHook('beforeSave');

            $this->profil->update($data);

            $this->callHook('afterSave');
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction() ?
                $this->rollBackDatabaseTransaction() :
                $this->commitDatabaseTransaction();

            return;
        } catch (Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }

        $this->commitDatabaseTransaction();

        $this->getSavedNotification()?->send();
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Profil desa tersimpan.');
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->operation('edit')
            ->model($this->profil)
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('desa_id')
                    ->label('Desa')
                    ->options(fn (): array => DesaScoping::desaOptions())
                    ->required()
                    ->live()
                    ->visible(fn (): bool => DesaScoping::isDeveloperContext())
                    ->afterStateUpdated(function ($state, $livewire): void {
                        if ($livewire instanceof KelolaProfil && filled($state)) {
                            $livewire->switchDesa((int) $state);
                        }
                    }),
                Textarea::make('sejarah')
                    ->label('Sejarah')
                    ->rows(8)
                    ->columnSpanFull()
                    ->helperText('Teks biasa. Baris kosong menjadi paragraf baru.'),
                Textarea::make('visi')
                    ->label('Visi')
                    ->rows(4)
                    ->columnSpanFull()
                    ->helperText('Teks biasa. Baris kosong menjadi paragraf baru.'),
                RichEditor::make('misi')
                    ->label('Misi')
                    ->toolbarButtons([['bold', 'italic'], ['orderedList', 'bulletList'], ['undo', 'redo']])
                    ->columnSpanFull()
                    ->helperText('Gunakan tombol daftar bernomor atau berpoin; satu poin per baris.'),
                Media::upload('logo_path', 'profil', 'Logo Desa')
                    ->columnSpanFull()
                    ->helperText('Tampil di header dan footer situs (jpg/png/webp, maks. 5 MB). Disarankan PNG transparan. Kosongkan untuk memakai huruf inisial.'),
                Media::upload('foto_path', 'profil', 'Foto Sejarah')
                    ->columnSpanFull()
                    ->helperText('Foto pendamping seksi Sejarah dan kartu "Sekilas Desa" di beranda (jpg/png/webp, maks. 5 MB). Kosongkan untuk memakai placeholder.'),
            ]);
    }

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan')
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make($this->getFormActions())->key('form-actions'),
            ]);
    }
}
