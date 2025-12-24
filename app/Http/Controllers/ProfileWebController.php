<?php

namespace App\Http\Controllers;

use App\Models\ProfileWeb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProfileWebController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        $profile = ProfileWeb::firstOrFail();

        return Inertia::render('Profile/Edit', [
            'profile' => $profile,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name'    => 'required|string|max:255',
            'description' => 'required|string',
            'logo'        => 'nullable|image|mimes:jpeg,png,gif|max:2048',
        ]);

        $profile = ProfileWeb::firstOrFail();

        // dd($validated);
        // Handle logo upload
        if ($request->hasFile('logo')) {

            // Hapus logo lama jika ada
            if ($profile->logo_path && Storage::disk('public')->exists($profile->logo_path)) {
                Storage::disk('public')->delete($profile->logo_path);
            }

            // Simpan logo baru
            $file = $request->file('logo');

            $filename = 'logo.' . $file->getClientOriginalExtension();

            $path = $file->storeAs(
                'logos',
                $filename,
                'public'
            );

            $validated['logo_path'] = $path;
        }

        $profile->update($validated);

        Cache::forget('profile_web');

        return back()->with('status', 'Profile updated successfully!');
    }
}
