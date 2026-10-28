<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\ActivityLog;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        $request->user()->save();

        $changed = ActivityLogger::changedLabels($request->user(), ['name' => 'Nama', 'email' => 'Email']);

        if ($changed !== '') {
            ActivityLogger::record(ActivityLog::ACTION_PROFILE, 'Profil', "Mengubah informasi profil (yang diubah: {$changed})");
        }

        return Redirect::route('admin.profile.edit')->with('status', 'profile-updated');
    }
}
