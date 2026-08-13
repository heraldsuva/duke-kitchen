<?php

namespace App\Http\Controllers;

use App\Http\Requests\Site\StoreSiteRequest;
use App\Http\Requests\Site\UpdateSiteRequest;
use App\Models\Site;
use App\Models\User;
use App\Services\SiteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SiteController extends Controller
{
    public function __construct(private SiteService $siteService) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Site::class);

        $user = $request->user();

        $sites = Site::query()
            ->ownedBy($user)
            ->with('user:id,first_name,last_name,user_name')
            ->withCount('registeredEquipment')
            ->orderBy('description')
            ->get()
            ->map(fn (Site $site) => [
                'id' => $site->id,
                'description' => $site->description,
                'active' => $site->active,
                'equipment_count' => $site->registered_equipment_count,
                'owner' => [
                    'id' => $site->user->id,
                    'name' => $site->user->full_name,
                    'user_name' => $site->user->user_name,
                ],
            ]);

        return Inertia::render('Sites/Index', [
            'sites' => $sites,
            'owners' => $user->isSuperAdmin()
                ? User::query()
                    ->orderBy('first_name')
                    ->get(['id', 'first_name', 'last_name', 'user_name'])
                    ->map(fn (User $owner) => [
                        'id' => $owner->id,
                        'name' => $owner->full_name,
                        'user_name' => $owner->user_name,
                    ])
                : [],
            'isSuperAdmin' => $user->isSuperAdmin(),
        ]);
    }

    public function store(StoreSiteRequest $request): RedirectResponse
    {
        $this->siteService->create($request->user(), $request->validated());

        return redirect()
            ->route('sites.index')
            ->with('success', 'Site created successfully.');
    }

    public function update(UpdateSiteRequest $request, Site $site): RedirectResponse
    {
        $this->siteService->update($site, $request->validated());

        return redirect()
            ->route('sites.index')
            ->with('success', 'Site updated successfully.');
    }

    public function destroy(Site $site): RedirectResponse
    {
        $this->authorize('delete', $site);

        $this->siteService->delete($site);

        return redirect()
            ->route('sites.index')
            ->with('success', 'Site deleted. Equipment was unregistered but not deleted.');
    }
}
