<?php

declare(strict_types=1);

namespace App\Domains\Communities\Observers;

use App\Domains\Communities\Models\CommunityMember;

class CommunityMemberObserver
{
    public function creating(CommunityMember $member): void
    {
        $member->joined_at ??= now();
    }

    public function created(CommunityMember $member): void
    {
        $member->community()->increment('members_count');
    }

    public function deleted(CommunityMember $member): void
    {
        $member->community()->decrement('members_count');
    }
}
