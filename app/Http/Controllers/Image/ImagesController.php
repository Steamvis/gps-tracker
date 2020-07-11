<?php

namespace App\Http\Controllers\Image;

use App\Http\Controllers\Controller;
use App\Services\FileSystem\DestroyImage;
use App\Services\FileSystem\UploadImage;
use Illuminate\Http\Request;

class ImagesController extends Controller
{
    public function upload(Request $request)
    {
        $path = app(UploadImage::class)->execute(['image' => $request->image]);

        if (is_string($path)) {
            return $path;
        }

        return false;
    }

    public function destroy(Request $request)
    {
        // TODO remove dependency on car
        return app(DestroyImage::class)->execute(['car' => $request->car, 'image' => $request->image,]);
    }
}
