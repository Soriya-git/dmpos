<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfilePictureController extends Controller
{
    public function update(Request $request)
    {
        $data = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();
        $file = $data['image'];
        $extension = $file->getClientOriginalExtension();
        $name = Str::slug($user->name) ?: 'profile';
        $fileName = now()->format('YmdHis').'_'.$name.'.'.$extension;
        $path = $file->storeAs('user-images', $fileName, 'public');

        if ($user->image) {
            Storage::disk('public')->delete($user->image);
        }

        $user->update(['image' => $path]);

        return back()->with('success', 'Profile picture updated.');
    }
}
