<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SiteController extends Controller
{
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
}
