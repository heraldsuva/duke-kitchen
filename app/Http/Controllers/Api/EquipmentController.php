<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Equipment\StoreEquipmentRequest;
use App\Http\Requests\Equipment\UpdateEquipmentRequest;
use App\Models\Equipment;
use App\Services\EquipmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function __construct(private EquipmentService $equipmentService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Equipment::class);

        $equipment = Equipment::query()
            ->ownedBy($request->user())
            ->with(['registration.site', 'user:id,first_name,last_name'])
            ->orderBy('serial_number')
            ->get()
            ->map(fn (Equipment $item) => $this->formatEquipment($item));

        return response()->json([
            'data' => $equipment,
        ]);
    }

    public function store(StoreEquipmentRequest $request): JsonResponse
    {
        $equipment = $this->equipmentService->create($request->user(), $request->validated());

        return response()->json([
            'message' => 'Equipment created successfully.',
            'data' => $this->formatEquipment($equipment->load(['registration.site', 'user'])),
        ], 201);
    }

    public function update(UpdateEquipmentRequest $request, Equipment $equipment): JsonResponse
    {
        $equipment = $this->equipmentService->update($request->user(), $equipment, $request->validated());

        return response()->json([
            'message' => 'Equipment updated successfully.',
            'data' => $this->formatEquipment($equipment->load(['registration.site', 'user'])),
        ]);
    }

    public function destroy(Equipment $equipment): JsonResponse
    {
        $this->authorize('delete', $equipment);

        $this->equipmentService->delete($equipment);

        return response()->json([
            'message' => 'Equipment deleted successfully.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatEquipment(Equipment $item): array
    {
        return [
            'id' => $item->id,
            'serial_number' => $item->serial_number,
            'description' => $item->description,
            'condition' => $item->condition->value,
            'site_id' => $item->registration?->site_id,
            'site' => $item->registration?->site?->description,
            'owner' => $item->user->full_name,
            'user_id' => $item->user_id,
        ];
    }
}
