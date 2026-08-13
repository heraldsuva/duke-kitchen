<?php

namespace App\Models;

use Database\Factories\RegisteredEquipmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $equipment_id
 * @property int $site_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Equipment $equipment
 * @property-read Site $site
 */
#[Fillable(['equipment_id', 'site_id'])]
class RegisteredEquipment extends Model
{
    /** @use HasFactory<RegisteredEquipmentFactory> */
    use HasFactory;

    protected $table = 'registered_equipment';

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
