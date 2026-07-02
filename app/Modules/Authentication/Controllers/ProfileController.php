<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Address\Models\City;
use App\Modules\Address\Models\Province;
use App\Modules\Authentication\Requests\ProfileRequest;
use App\Modules\Authentication\Services\ProfileAvatarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        protected ProfileAvatarService $avatars,
    ) {}

    public function show(): View
    {
        $user = auth()->user()->load(['avatar', 'province', 'city']);

        return view('authentication::profile.show', compact('user'));
    }

    public function edit(): View
    {
        $user = auth()->user()->load(['avatar', 'province', 'city']);
        $provinces = Province::query()->active()->orderBy('name')->get();
        $cities = $user->province_id
            ? City::query()->active()->where('province_id', $user->province_id)->orderBy('name')->get()
            : collect();

        return view('authentication::profile.edit', compact('user', 'provinces', 'cities'));
    }

    public function update(ProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $emailChanged = $user->email !== $request->email;

        $this->avatars->sync(
            $user,
            $request->input('avatar_media_id') ? (int) $request->input('avatar_media_id') : null,
            $request->boolean('remove_avatar'),
            $request->file('avatar'),
        );

        $user->fill($request->profileAttributes());

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();
        $this->avatars->registerUsage($user);

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();

            return redirect()->route('admin.profile.show')
                ->with('success', 'Profile updated. Please verify your new email address.');
        }

        return redirect()->route('admin.profile.show')
            ->with('success', 'Profile updated successfully.');
    }
}
