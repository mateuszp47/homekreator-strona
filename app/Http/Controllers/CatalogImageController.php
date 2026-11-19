<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class CatalogImageController extends Controller
{
    public function __invoke(string $filename)
    {
        // Only generated catalog images are public; never expose arbitrary storage files.
        abort_unless(preg_match('/\A[a-zA-Z0-9_-]+\.(?:webp|jpg|jpeg|png)\z/', $filename), 404);

        $disk = Storage::disk('public');
        $path = 'catalog/'.$filename;
        abort_unless($disk->exists($path), 404);

        return $disk->response($path, null, [
            'Cache-Control' => 'public, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
