<?php

declare(strict_types=1);

namespace App\Domains\Communities\Services;

use App\Domains\Communities\Enums\CommunityApplicationStatus;
use App\Domains\Communities\Enums\CommunityMemberRole;
use App\Domains\Communities\Enums\CommunityStatus;
use App\Domains\Communities\Models\Community;
use App\Domains\Communities\Models\CommunityApplication;
use App\Domains\Users\Models\User;
use Illuminate\Support\Facades\DB;

class CommunityService
{
    /** Одобрить заявку: создать сообщество и сделать заявителя владельцем. */
    public function approveApplication(CommunityApplication $application, User $reviewer): Community
    {
        return DB::transaction(function () use ($application, $reviewer): Community {
            $community = Community::create([
                'owner_id' => $application->user_id,
                'category_id' => $application->category_id,
                'name' => $application->proposed_name,
                'description' => $application->description,
                'status' => CommunityStatus::Active->value,
            ]);

            $this->addMember($community, $application->user, CommunityMemberRole::Moderator);

            $application->forceFill([
                'status' => CommunityApplicationStatus::Approved->value,
                'community_id' => $community->id,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ])->save();

            return $community;
        });
    }

    public function rejectApplication(CommunityApplication $application, User $reviewer, ?string $reason): CommunityApplication
    {
        $application->forceFill([
            'status' => CommunityApplicationStatus::Rejected->value,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'reason' => $reason,
        ])->save();

        return $application;
    }

    public function addMember(Community $community, User $user, CommunityMemberRole $role = CommunityMemberRole::Member): void
    {
        $community->members()->updateOrCreate(
            ['user_id' => $user->id],
            ['role' => $role->value, 'joined_at' => now()],
        );
    }
}
