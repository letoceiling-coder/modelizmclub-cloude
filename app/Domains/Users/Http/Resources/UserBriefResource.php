<?php

declare(strict_types=1);

namespace App\Domains\Users\Http\Resources;

use App\Domains\Users\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Краткая карточка пользователя для встраивания (автор поста, комментария и т.д.).
 *
 * @mixin User
 */
class UserBriefResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'avatar_url' => $this->avatar_path ? asset('storage/'.$this->avatar_path) : null,
        ];
    }
}
