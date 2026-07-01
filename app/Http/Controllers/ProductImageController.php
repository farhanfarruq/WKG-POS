<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductImageController extends Controller
{
    public function __invoke(string $path): StreamedResponse
    {
        $path = ltrim($path, '/');

        abort_unless($path !== '' && str_starts_with($path, 'products/') && ! in_array('..', explode('/', $path), true), 404);

        foreach (['public', 'local'] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->response($path);
            }
        }

        abort(404);
    }
}
