<?php

namespace App\Models;

use App\Concerns\BelongsToDesa;
use Illuminate\Database\Eloquent\Model;

class Profil extends Model
{
    use BelongsToDesa;

    protected $fillable = ['desa_id', 'sejarah', 'visi', 'misi', 'foto_path', 'logo_path'];

    public static function forDesa(Desa $desa): self
    {
        return static::withoutGlobalScope('desa')->firstOrCreate([
            'desa_id' => $desa->id,
        ]);
    }

    public function isEmpty(): bool
    {
        return trim((string) $this->sejarah) === ''
            && trim((string) $this->visi) === ''
            && trim((string) $this->misi) === '';
    }
}
