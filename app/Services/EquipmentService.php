<?php

namespace App\Services;

use App\Enums\EquipmentCondition;
use App\Models\Equipment;
use App\Models\RegisteredEquipment;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EquipmentService
{
    /**
     * @param  array{serial_number: string, description: string, condition: string, site_id?: int|null}  $data
     */
    public function create(User $actor, array $data): Equipment
    {
        return DB::transaction(function () use ($actor, $data): Equipment {
            $site = $this->resolveAccessibleSite($actor, $data['site_id'] ?? null);

            // Admins always own what they create. SuperAdmins inherit the site
            // owner's user_id when registering to a site so ownership stays aligned.
            $ownerId = $site !== null && $actor->isSuperAdmin()
                ? $site->user_id
                : $actor->id;

            $equipment = Equipment::query()->create([
                'serial_number' => $data['serial_number'],
                'description' => $data['description'],
                'condition' => EquipmentCondition::from($data['condition']),
                'user_id' => $ownerId,
            ]);

            if ($site !== null) {
                $this->assertSameOwner($equipment, $site);

                RegisteredEquipment::query()->create([
                    'equipment_id' => $equipment->id,
                    'site_id' => $site->id,
                ]);
            }

            return $equipment->load('registration.site');
        });
    }

    /**
     * @param  array{serial_number: string, description: string, condition: string, site_id?: int|null}  $data
     */
    public function update(User $actor, Equipment $equipment, array $data): Equipment
    {
        return DB::transaction(function () use ($actor, $equipment, $data): Equipment {
            $site = $this->resolveAccessibleSite($actor, $data['site_id'] ?? null);

            if ($site !== null && $site->user_id !== $equipment->user_id) {
                throw ValidationException::withMessages([
                    'site_id' => 'Equipment and site must belong to the same user.',
                ]);
            }

            $equipment->update([
                'serial_number' => $data['serial_number'],
                'description' => $data['description'],
                'condition' => EquipmentCondition::from($data['condition']),
            ]);

            $this->syncRegistration($equipment->fresh(), $site);

            return $equipment->fresh(['registration.site']);
        });
    }

    public function delete(Equipment $equipment): void
    {
        DB::transaction(function () use ($equipment): void {
            $equipment->registration?->delete();
            $equipment->delete();
        });
    }

    private function resolveAccessibleSite(User $actor, mixed $siteId): ?Site
    {
        if ($siteId === null || $siteId === '') {
            return null;
        }

        $site = Site::query()->find((int) $siteId);

        if ($site === null) {
            throw ValidationException::withMessages([
                'site_id' => 'The selected site does not exist.',
            ]);
        }

        if (! $actor->isSuperAdmin() && $site->user_id !== $actor->id) {
            throw ValidationException::withMessages([
                'site_id' => 'This site belongs to another user.',
            ]);
        }

        return $site;
    }

    private function assertSameOwner(Equipment $equipment, Site $site): void
    {
        if ($equipment->user_id !== $site->user_id) {
            throw ValidationException::withMessages([
                'site_id' => 'Equipment and site must belong to the same user.',
            ]);
        }
    }

    private function syncRegistration(Equipment $equipment, ?Site $site): void
    {
        $registration = $equipment->registration;

        if ($site === null) {
            $registration?->delete();

            return;
        }

        $this->assertSameOwner($equipment, $site);

        if ($registration === null) {
            RegisteredEquipment::query()->create([
                'equipment_id' => $equipment->id,
                'site_id' => $site->id,
            ]);

            return;
        }

        if ($registration->site_id !== $site->id) {
            $registration->update(['site_id' => $site->id]);
        }
    }
}
