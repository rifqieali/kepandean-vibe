<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Desa extends Model
{
    protected $fillable = ['name', 'slug', 'domain', 'is_default'];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /** @return HasMany<User, $this> */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first()
            ?? static::where('slug', 'kepandean')->first()
            ?? static::first();
    }

    public static function resolveFromDomain(string $domain): ?self
    {
        return static::where('domain', $domain)->first()
            ?? static::getDefault();
    }
}
