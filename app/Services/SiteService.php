<?php

namespace App\Services;

use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SiteService
{
    /**
     * @param  array{description: string, active: bool, user_id?: int|null}  $data
     */
    public function create(User $actor, array $data): Site
    {
        $ownerId = $actor->isSuperAdmin() && isset($data['user_id'])
            ? (int) $data['user_id']
            : $actor->id;

        if ($actor->isAdmin() && isset($data['user_id']) && (int) $data['user_id'] !== $actor->id) {
            throw new InvalidArgumentException('You cannot create a site for another user.');
        }

        return Site::query()->create([
            'user_id' => $ownerId,
            'description' => $data['description'],
            'active' => $data['active'],
        ]);
    }

    /**
     * @param  array{description: string, active: bool}  $data
     */
    public function update(Site $site, array $data): Site
    {
        $site->update([
            'description' => $data['description'],
            'active' => $data['active'],
        ]);

        return $site->refresh();
    }

    /**
     * Delete a site and unregister all of its equipment.
     * Equipment records themselves are preserved.
     */
    public function delete(Site $site): void
    {
        DB::transaction(function () use ($site): void {
            $site->registeredEquipment()->delete();
            $site->delete();
        });
    }
}
