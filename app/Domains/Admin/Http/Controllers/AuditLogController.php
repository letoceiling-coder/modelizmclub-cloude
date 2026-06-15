<?php

declare(strict_types=1);

namespace App\Domains\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $activities = Activity::query()
            ->with(['causer', 'subject'])
            ->when($request->filled('log_name'), fn ($q) => $q->where('log_name', $request->string('log_name')->toString()))
            ->latest()
            ->paginate(50);

        $activities->getCollection()->transform(fn (Activity $activity): array => [
            'id' => $activity->id,
            'log_name' => $activity->log_name,
            'description' => $activity->description,
            'subject_type' => $activity->subject_type,
            'subject_id' => $activity->subject_id,
            'causer_id' => $activity->causer_id,
            'properties' => $activity->properties,
            'created_at' => $activity->created_at?->toIso8601String(),
        ]);

        return response()->json($activities);
    }
}
