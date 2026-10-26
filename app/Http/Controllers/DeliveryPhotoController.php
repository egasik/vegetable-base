<?php

namespace App\Http\Controllers;

use App\Models\DeliveryPhoto;
use Illuminate\Support\Facades\Storage;

class DeliveryPhotoController extends Controller
{
    public function show(DeliveryPhoto $photo)
    {
        abort_unless(auth()->user()->role === 'admin' || $photo->order?->user_id === auth()->id(), 403);
        abort_unless(Storage::disk('local')->exists($photo->file_path), 404);

        return response()->file(Storage::disk('local')->path($photo->file_path), ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
