<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    /**
     * Upload an image for use inside page sections.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:png,jpg,jpeg,webp,gif', 'max:4096'],
        ]);

        $path = $request->file('image')->store('pages', 'uploads');

        return response()->json(['url' => '/uploads/'.$path], 201);
    }
}
