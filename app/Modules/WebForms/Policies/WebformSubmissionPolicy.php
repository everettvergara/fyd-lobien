<?php

namespace App\Modules\WebForms\Policies;

use App\Models\User;
use App\Modules\WebForms\Models\WebformSubmission;

class WebformSubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('webforms.submissions.view');
    }

    public function view(User $user, WebformSubmission $webformSubmission): bool
    {
        return $user->hasPermission('webforms.submissions.view');
    }

    public function delete(User $user, WebformSubmission $webformSubmission): bool
    {
        return $user->hasPermission('webforms.submissions.delete');
    }
}
