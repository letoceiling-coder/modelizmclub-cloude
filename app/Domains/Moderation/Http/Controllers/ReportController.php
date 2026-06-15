<?php

declare(strict_types=1);

namespace App\Domains\Moderation\Http\Controllers;

use App\Domains\Moderation\Enums\ReportStatus;
use App\Domains\Moderation\Http\Requests\StoreReportRequest;
use App\Domains\Moderation\Http\Resources\ReportResource;
use App\Domains\Moderation\Models\Report;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReportController extends Controller
{
    public function store(StoreReportRequest $request): JsonResponse
    {
        $report = Report::create([
            'reporter_id' => $request->user()->id,
            'reportable_type' => $request->input('target_type'),
            'reportable_id' => $request->integer('target_id'),
            'reason' => $request->string('reason')->toString(),
            'description' => $request->input('description'),
            'status' => ReportStatus::Open->value,
        ]);

        return ReportResource::make($report)->response()->setStatusCode(201);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Report::query()->with('reporter')->latest();

        $status = $request->string('status')->toString();
        if ($status !== '') {
            $query->where('status', $status);
        } else {
            $query->open();
        }

        return ReportResource::collection($query->paginate(30));
    }

    public function resolve(Request $request, Report $report): ReportResource
    {
        return $this->close($request, $report, ReportStatus::Resolved);
    }

    public function dismiss(Request $request, Report $report): ReportResource
    {
        return $this->close($request, $report, ReportStatus::Dismissed);
    }

    private function close(Request $request, Report $report, ReportStatus $status): ReportResource
    {
        $data = $request->validate([
            'resolution' => ['nullable', 'string', 'max:1000'],
        ]);

        $report->forceFill([
            'status' => $status->value,
            'handled_by' => $request->user()->id,
            'handled_at' => now(),
            'resolution' => $data['resolution'] ?? null,
        ])->save();

        return ReportResource::make($report->load(['reporter', 'handler']));
    }
}
