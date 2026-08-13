<?php

namespace App\Http\Controllers;

use App\Enums\EquipmentCondition;
use App\Http\Requests\Equipment\StoreEquipmentRequest;
use App\Http\Requests\Equipment\UpdateEquipmentRequest;
use App\Models\Equipment;
use App\Models\Site;
use App\Services\EquipmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EquipmentController extends Controller
{
    public function __construct(private EquipmentService $equipmentService) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Equipment::class);

        $user = $request->user();

        $equipment = Equipment::query()
            ->ownedBy($user)
            ->with(['registration.site', 'user:id,first_name,last_name'])
            ->orderBy('serial_number')
            ->get()
            ->map(fn (Equipment $item) => [
                'id' => $item->id,
                'serial_number' => $item->serial_number,
                'description' => $item->description,
                'condition' => $item->condition->value,
                'site_id' => $item->registration?->site_id,
                'site' => $item->registration?->site?->description,
                'owner' => $item->user->full_name,
                'user_id' => $item->user_id,
            ]);

        $sites = Site::query()
            ->ownedBy($user)
            ->orderBy('description')
            ->get(['id', 'description', 'user_id', 'active'])
            ->map(fn (Site $site) => [
                'id' => $site->id,
                'description' => $site->description,
                'user_id' => $site->user_id,
                'active' => $site->active,
            ]);

        return Inertia::render('Equipment/Index', [
            'equipment' => $equipment,
            'sites' => $sites,
            'conditions' => collect(EquipmentCondition::cases())->map(fn (EquipmentCondition $condition) => [
                'label' => $condition->label(),
                'value' => $condition->value,
            ]),
            'isSuperAdmin' => $user->isSuperAdmin(),
        ]);
    }

    public function store(StoreEquipmentRequest $request): RedirectResponse
    {
        $this->equipmentService->create($request->user(), $request->validated());

        return redirect()
            ->route('equipment.index')
            ->with('success', 'Equipment created successfully.');
    }

    public function update(UpdateEquipmentRequest $request, Equipment $equipment): RedirectResponse
    {
        $this->equipmentService->update($request->user(), $equipment, $request->validated());

        return redirect()
            ->route('equipment.index')
            ->with('success', 'Equipment updated successfully.');
    }

    public function destroy(Equipment $equipment): RedirectResponse
    {
        $this->authorize('delete', $equipment);

        $this->equipmentService->delete($equipment);

        return redirect()
            ->route('equipment.index')
            ->with('success', 'Equipment deleted successfully.');
    }
}
