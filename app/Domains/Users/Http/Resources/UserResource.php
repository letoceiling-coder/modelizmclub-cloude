<?php

declare(strict_types=1);

namespace App\Domains\Users\Http\Resources;

use App\Domains\Catalog\Http\Resources\CategoryResource;
use App\Domains\Users\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isSelf = $request->user()?->id === $this->id;
        $privacy = $this->privacy ?? [];

        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'avatar_url' => $this->avatar_url,
            'bio' => $this->bio,
            'city' => $this->whenLoaded('city', fn () => [
                'id' => $this->city->id,
                'name' => $this->city->name,
            ]),
            'gender' => $this->gender?->value,
            'rating' => $this->rating,
            'status' => $this->status?->value,
            'roles' => $this->whenLoaded('roles', fn () => $this->getRoleNames()),
            'is_subscriber' => $this->when($isSelf, fn () => $this->isSubscriber()),
            'interests' => CategoryResource::collection($this->whenLoaded('interests')),
            'counts' => $this->when($isSelf, fn () => [
                'posts' => $this->posts()->count(),
                'communities' => $this->communityMemberships()->count(),
            ]),
            // Приватные поля — только владельцу либо согласно настройкам приватности
            'email' => $this->when($isSelf || ($privacy['show_email'] ?? false), $this->email),
            'phone' => $this->when($isSelf || ($privacy['show_phone'] ?? false), $this->phone),
            'birthdate' => $this->when($isSelf || ($privacy['show_birthdate'] ?? false), $this->birthdate?->toDateString()),
            'email_verified' => $this->when($isSelf, fn () => $this->email_verified_at !== null),
            'privacy' => $this->when($isSelf, fn () => $privacy),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
