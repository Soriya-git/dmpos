<?php

namespace App\Http\Controllers\MasterData;

use App\Models\DiningResource;
use App\Models\DiningResourceType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DiningResourceController
{
    public function index(Request $request): Response
    {
        $companyId = $request->user()?->company_id;

        $resources = DiningResource::query()
            ->with([
                'branch:id,name,code',
                'diningResourceType:id,name,code',
                'activeSession:id,dining_resource_id,session_no,status,guest_count,opened_at,opened_by',
                'activeSession.opener:id,name',
            ])
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->orderBy('name')
            ->get()
            ->map(function (DiningResource $resource): array {
                $session = $resource->activeSession;

                return [
                    'id' => $resource->id,
                    'code' => $resource->code ?: 'SEAT-'.str_pad((string) $resource->id, 3, '0', STR_PAD_LEFT),
                    'name' => $resource->name,
                    'branch' => $resource->branch?->name ?? 'Unassigned',
                    'type' => $resource->diningResourceType?->name ?? 'Seat',
                    'capacity' => (int) ($resource->capacity ?? 0),
                    'seatStatus' => $resource->status,
                    'currentSessionNo' => $session?->session_no,
                    'sessionStatus' => $session?->status,
                    'guestCount' => $session?->guest_count,
                    'openedBy' => $session?->opener?->name,
                    'openedAt' => $session?->opened_at?->toDateTimeString(),
                    'description' => $resource->description,
                    'status' => $resource->is_active ? 'approved' : 'cancelled',
                ];
            });

        $types = DiningResourceType::query()
            ->withCount('diningResources')
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->orderBy('name')
            ->get()
            ->map(fn (DiningResourceType $type): array => [
                'id' => $type->id,
                'code' => $type->code ?: 'TYPE-'.str_pad((string) $type->id, 3, '0', STR_PAD_LEFT),
                'name' => $type->name,
                'description' => $type->description,
                'resourcesCount' => (int) $type->dining_resources_count,
                'status' => $type->is_active ? 'approved' : 'cancelled',
            ]);

        return Inertia::render('MasterData/Seats', [
            'resources' => $resources,
            'types' => $types,
        ]);
    }
}
