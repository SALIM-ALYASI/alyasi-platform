<?php

if (! function_exists('media_url')) {
    /**
     * Resolve a content-image path to a public URL, regardless of whether
     * it was seeded as a plain public asset path (e.g. `images/home/x.webp`)
     * or saved by an upload flow as a path relative to the `public` storage
     * disk (e.g. `works/covers/x.jpg`). Mixing both formats in the same
     * database column (as WorkDemoSeeder vs. the admin upload flow do for
     * `works.cover_image`) is what causes 404s if only one format is assumed.
     */
    function media_url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        $path = trim($path);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:')) {
            return $path;
        }

        $normalized = ltrim($path, '/');

        if (str_starts_with($normalized, 'images/') || str_starts_with($normalized, 'assets/') || str_starts_with($normalized, 'uploads/') || str_starts_with($normalized, 'storage/')) {
            return asset($normalized);
        }

        return \Illuminate\Support\Facades\Storage::url($normalized);
    }
}

if (! function_exists('versioned_asset')) {
    /**
     * Build an asset URL with a cache-busting query string based on the
     * file's last modification time, so browsers fetch the latest version
     * of static CSS/JS files instead of serving a stale cached copy.
     */
    function versioned_asset(string $path): string
    {
        $fullPath = public_path($path);

        if (! file_exists($fullPath)) {
            return asset($path);
        }

        return asset($path).'?v='.filemtime($fullPath);
    }
}
