<?php

declare(strict_types=1);

namespace App\Domains\Communities\Policies;

use App\Domains\Communities\Enums\CommunityMemberRole;
use App\Domains\Communities\Models\Community;
use App\Domains\Users\Models\User;

class CommunityPolicy
{
    public function update(User $user, Community $community): bool
    {
        return $this->manages($user, $community);
    }

    public function delete(User $user, Community $community): bool
    {
        return $community->owner_id === $user->id || $user->can('communities.manage');
    }

    public function manageSections(User $user, Community $community): bool
    {
        return $this->manages($user, $community);
    }

    public function manageMembers(User $user, Community $community): bool
    {
        return $this->manages($user, $community);
    }

    /** Владелец, модератор сообщества или администратор платформы. */
    private function manages(User $user, Community $community): bool
    {
        if ($community->owner_id === $user->id || $user->can('communities.manage')) {
            return true;
        }

        return $community->members()
            ->where('user_id', $user->id)
            ->where('role', CommunityMemberRole::Moderator->value)
            ->exists();
    }
}
