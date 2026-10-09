<?php

namespace App\Models;

use App\Concerns\BelongsToDesa;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

class Asset extends Model implements HasMedia
{
    use BelongsToDesa, InteractsWithMedia;

    protected $guarded = ['id'];

    public function registerMediaCollections(): void
    {
        // Originals stay on the private disk (config media-library.disk_name);
        // only conversion variants land on the public/CDN disk
        // (config media-library.conversions_disk_name).
        $this->addMediaCollection('uploads')
            ->registerMediaConversions(function (SpatieMedia $media) {
                $this->addMediaConversion('thumb')
                    ->queued()
                    ->width(368)
                    ->height(232)
                    ->format('webp');

                $this->addMediaConversion('preview')
                    ->queued()
                    ->width(800)
                    ->height(600)
                    ->format('webp');

                $this->addMediaConversion('responsive')
                    ->queued()
                    ->withResponsiveImages()
                    ->format('webp');
            });
    }
}
