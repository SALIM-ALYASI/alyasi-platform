<?php

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
