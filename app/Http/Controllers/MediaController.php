<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'media' => ['required', 'array'],
            'media.*' => ['file', 'mimes:jpeg,png,gif,webp,mp4,mov,avi', 'max:10240'],
        ]);

        $paths = [];

        foreach ($request->file('media') as $file) {
            $paths[] = $file->store('uploads', 'public');
        }

        return response()->json(['paths' => $paths]);
    }
}
