<?php

namespace App\Http\Controllers\Image;

use App\Http\Controllers\Controller;
use App\Services\FileSystem\DestroyImage;
use Illuminate\Http\Request;

use RealRashid\SweetAlert\Facades\Alert;

use function GuzzleHttp\Promise\all;

class ImageController extends Controller
{
    public function destroy(Request $request)
    {
        return app(DestroyImage::class)->execute(['car' => $request->car, 'image' => $request->image,]);
    }
}
