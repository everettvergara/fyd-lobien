<?php

namespace App\Modules\Authentication\Services;

use App\Models\Media;
use App\Models\MediaFolder;
use App\Models\MediaUsage;
use App\Models\User;
use App\Services\Media\MediaLibraryService;
use App\Services\Media\MediaUsageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ProfileAvatarService
{
    public function __construct(
        protected MediaLibraryService $media,
        protected MediaUsageService $usage,
    ) {}

    public function sync(User $user, ?int $avatarMediaId, bool $removeAvatar, ?UploadedFile $uploadedFile): void
    {
        if ($removeAvatar) {
            $this->clearAvatar($user);

            return;
        }

        if ($uploadedFile) {
            $media = $this->uploadAvatar($uploadedFile, $user->id);
            $user->avatar_media_id = $media->id;

            return;
        }

        if ($avatarMediaId) {
            $user->avatar_media_id = $avatarMediaId;

            return;
        }

        if ($this->hasSubmittedAvatarField() && ! $avatarMediaId) {
            $this->clearAvatar($user);
        }
    }

    public function registerUsage(User $user): void
    {
        if ($user->avatar_media_id) {
            $this->usage->register($user->avatar_media_id, $user, 'profile', 'avatar_media_id', 'Profile Photo');

            return;
        }

        MediaUsage::where([
            'usable_type' => User::class,
            'usable_id' => $user->getKey(),
            'field' => 'avatar_media_id',
        ])->delete();
    }

    protected function uploadAvatar(UploadedFile $file, int $userId): Media
    {
        $folder = MediaFolder::firstOrCreate(
            ['slug' => 'profile-avatars'],
            [
                'name' => 'Profile Avatars',
                'sort_order' => 0,
                'created_by' => $userId,
            ],
        );

        return $this->media->upload($file, [
            'folder_id' => $folder->id,
            'title' => 'Profile avatar',
            'alt_text' => 'Profile photo',
        ], $userId);
    }

    protected function clearAvatar(User $user): void
    {
        $user->avatar_media_id = null;
    }

    protected function hasSubmittedAvatarField(): bool
    {
        return request()->has('avatar_media_id');
    }
}
