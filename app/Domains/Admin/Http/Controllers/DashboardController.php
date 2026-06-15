<?php

declare(strict_types=1);

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Communities\Models\Community;
use App\Domains\Feed\Models\Comment;
use App\Domains\Feed\Models\Post;
use App\Domains\Moderation\Models\ModerationItem;
use App\Domains\Moderation\Models\Report;
use App\Domains\Users\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => [
                'users' => [
                    'total' => User::query()->count(),
                    'new_last_7_days' => User::query()->where('created_at', '>=', now()->subDays(7))->count(),
                ],
                'content' => [
                    'posts' => Post::query()->count(),
                    'comments' => Comment::query()->count(),
                    'communities' => Community::query()->count(),
                ],
                'moderation' => [
                    'pending_items' => ModerationItem::query()->pending()->count(),
                    'open_reports' => Report::query()->open()->count(),
                ],
            ],
        ]);
    }
}
