<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use App\Support\Filament\DesaScoping;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Surel')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(table: User::class, column: 'email', ignoreRecord: true),
                DesaScoping::desaSelect(),
                Select::make('role')
                    ->label('Role')
                    ->helperText('Hanya developer yang boleh memberi role developer.')
                    ->options(function (): array {
                        $user = auth()->user();

                        if ($user instanceof User && $user->isDeveloper()) {
                            return [
                                'developer' => 'Developer',
                                'admin_desa' => 'Admin Desa',
                                'editor' => 'Editor',
                            ];
                        }

                        return [
                            'admin_desa' => 'Admin Desa',
                            'editor' => 'Editor',
                        ];
                    })
                    ->required(),
                TextInput::make('password')
                    ->label('Kata sandi')
                    ->password()
                    ->revealable()
                    ->helperText('Kosongkan bila tidak ingin mengubah kata sandi.')
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create'),
            ]);
    }
}
