<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\StoreSiteRequest;
use App\Http\Requests\Site\UpdateSiteRequest;
use App\Models\Site;
use App\Services\SiteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function __construct(private SiteService $siteService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Site::class);

        $sites = Site::query()
            ->ownedBy($request->user())
            ->with('user:id,first_name,last_name,user_name')
            ->withCount('registeredEquipment')
            ->orderBy('description')
            ->get()
            ->map(fn (Site $site) => $this->formatSite($site));

        return response()->json([
            'data' => $sites,
        ]);
    }

    public function store(StoreSiteRequest $request): JsonResponse
    {
        $site = $this->siteService->create($request->user(), $request->validated());

        return response()->json([
            'message' => 'Site created successfully.',
            'data' => $this->formatSite($site->load('user')->loadCount('registeredEquipment')),
        ], 201);
    }

    public function update(UpdateSiteRequest $request, Site $site): JsonResponse
    {
        $site = $this->siteService->update($site, $request->validated());

        return response()->json([
            'message' => 'Site updated successfully.',
            'data' => $this->formatSite($site->load('user')->loadCount('registeredEquipment')),
        ]);
    }

    public function destroy(Site $site): JsonResponse
    {
        $this->authorize('delete', $site);

        $this->siteService->delete($site);

        return response()->json([
            'message' => 'Site deleted. Equipment was unregistered but not deleted.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatSite(Site $site): array
    {
        return [
            'id' => $site->id,
            'description' => $site->description,
            'active' => $site->active,
            'equipment_count' => $site->registered_equipment_count ?? $site->registeredEquipment()->count(),
            'owner' => [
                'id' => $site->user->id,
                'name' => $site->user->full_name,
                'user_name' => $site->user->user_name,
            ],
        ];
    }
}
