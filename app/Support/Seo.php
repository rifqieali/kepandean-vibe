<?php

namespace App\Support;

/**
 * Satu-satunya seam SEO publik (issue #20).
 *
 * Controller hanya memilih judul, deskripsi, URL kanonis, dan gambar OG.
 * Helper ini yang merakit meta unik plus schema.org JSON-LD, sehingga
 * tidak ada markup SEO yang di-hardcode di template React.
 */
final class Seo
{
    /**
     * @return array{title: string, description: string, canonical_url: string, og_image: ?string}
     */
    public static function meta(string $title, string $description, string $canonicalUrl, ?string $ogImage = null): array
    {
        return [
            'title' => trim($title),
            'description' => trim($description),
            'canonical_url' => $canonicalUrl,
            'og_image' => $ogImage,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function websiteSchema(string $name, string $url, ?string $description = null): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $name,
            'url' => $url,
        ];

        if (is_string($description) && $description !== '') {
            $schema['description'] = $description;
        }

        return $schema;
    }

    /**
     * @return array<string, mixed>
     */
    public static function articleSchema(
        string $headline,
        string $url,
        string $datePublished,
        ?string $description = null,
        ?string $image = null,
    ): array {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => $headline,
            'url' => $url,
            'datePublished' => $datePublished,
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Pemerintah Desa Kepandean',
            ],
        ];

        if (is_string($description) && $description !== '') {
            $schema['description'] = $description;
        }

        if (is_string($image) && $image !== '') {
            $schema['image'] = [$image];
        }

        return $schema;
    }

    /**
     * @return array<string, mixed>
     */
    public static function collectionSchema(string $name, string $url, ?string $description = null): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $name,
            'url' => $url,
        ];

        if (is_string($description) && $description !== '') {
            $schema['description'] = $description;
        }

        return $schema;
    }

    /**
     * @return array<string, mixed>
     */
    public static function profileSchema(string $name, string $url, ?string $description = null): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'GovernmentOrganization',
            'name' => $name,
            'url' => $url,
        ];

        if (is_string($description) && $description !== '') {
            $schema['description'] = $description;
        }

        return $schema;
    }
}
