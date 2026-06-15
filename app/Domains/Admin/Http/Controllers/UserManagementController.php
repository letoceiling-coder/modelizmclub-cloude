<?php

declare(strict_types=1);

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Admin\Http\Resources\AdminUserResource;
use App\Domains\Users\Enums\Role;
use App\Domains\Users\Enums\UserStatus;
use App\Domains\Users\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UserManagementController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $users = QueryBuilder::for(User::query())
            ->allowedFilters(
                AllowedFilter::exact('status'),
                AllowedFilter::callback('search', fn ($q, $value) => $q->search($value)),
                AllowedFilter::callback('role', fn ($q, $value) => $q->whereHas('roles', fn ($r) => $r->where('name', $value))),
            )
            ->allowedSorts('created_at', 'name', 'last_seen_at')
            ->defaultSort('-created_at')
            ->paginate(min((int) $request->integer('per_page', 25), 100))
            ->withQueryString();

        return AdminUserResource::collection($users);
    }

    public function show(User $user): AdminUserResource
    {
        return AdminUserResource::make($user);
    }

    public function ban(Request $request, User $user): AdminUserResource
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $user->forceFill(['status' => UserStatus::Banned->value])->save();
        $user->tokens()->delete();

        activity('admin')
            ->performedOn($user)
            ->causedBy($request->user())
            ->withProperties(['reason' => $data['reason'] ?? null])
            ->log('user.banned');

        return AdminUserResource::make($user);
    }

    public function unban(Request $request, User $user): AdminUserResource
    {
        $user->forceFill(['status' => UserStatus::Active->value])->save();

        activity('admin')
            ->performedOn($user)
            ->causedBy($request->user())
            ->log('user.unbanned');

        return AdminUserResource::make($user);
    }

    public function syncRoles(Request $request, User $user): AdminUserResource
    {
        $data = $request->validate([
            'roles' => ['present', 'array'],
            'roles.*' => [Rule::in(Role::values())],
        ]);

        $user->syncRoles($data['roles']);

        activity('admin')
            ->performedOn($user)
            ->causedBy($request->user())
            ->withProperties(['roles' => $data['roles']])
            ->log('user.roles_synced');

        return AdminUserResource::make($user->fresh());
    }
}
