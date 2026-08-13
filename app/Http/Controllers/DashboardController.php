<?php

namespace App\Http\Controllers;

use App\Enums\EquipmentCondition;
use App\Models\Equipment;
use App\Models\Site;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $sitesQuery = Site::query()->ownedBy($user);
        $equipmentQuery = Equipment::query()->ownedBy($user);

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_sites' => (clone $sitesQuery)->count(),
                'total_equipment' => (clone $equipmentQuery)->count(),
                'working_equipment' => (clone $equipmentQuery)->where('condition', EquipmentCondition::Working)->count(),
                'not_working_equipment' => (clone $equipmentQuery)->where('condition', EquipmentCondition::NotWorking)->count(),
            ],
            'recent_equipment' => (clone $equipmentQuery)->with(['registration.site'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (Equipment $equipment) => [
                    'id' => $equipment->id,
                    'serial_number' => $equipment->serial_number,
                    'description' => $equipment->description,
                    'condition' => $equipment->condition->value,
                    'site' => $equipment->registration?->site?->description,
                ]),
        ]);
    }
}
