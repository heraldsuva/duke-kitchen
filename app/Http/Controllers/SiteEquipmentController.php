<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Inertia\Inertia;
use Inertia\Response;

class SiteEquipmentController extends Controller
{
    public function __invoke(Site $site): Response
    {
        $this->authorize('manageEquipment', $site);

        $site->load([
            'user:id,first_name,last_name',
            'registeredEquipment.equipment',
        ]);

        return Inertia::render('Sites/Equipment', [
            'site' => [
                'id' => $site->id,
                'description' => $site->description,
                'active' => $site->active,
                'owner' => $site->user->full_name,
            ],
            'equipment' => $site->registeredEquipment
                ->map(fn ($registration) => [
                    'id' => $registration->equipment->id,
                    'serial_number' => $registration->equipment->serial_number,
                    'description' => $registration->equipment->description,
                    'condition' => $registration->equipment->condition->value,
                ])
                ->values(),
        ]);
    }
}
