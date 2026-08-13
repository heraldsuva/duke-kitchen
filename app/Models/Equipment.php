<?php

namespace App\Models;

use App\Enums\EquipmentCondition;
use Database\Factories\EquipmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $serial_number
 * @property string $description
 * @property EquipmentCondition $condition
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read RegisteredEquipment|null $registration
 */
#[Fillable(['serial_number', 'description', 'condition', 'user_id'])]
class Equipment extends Model
{
    /** @use HasFactory<EquipmentFactory> */
    use HasFactory;

    protected $table = 'equipment';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'condition' => EquipmentCondition::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registration(): HasOne
    {
        return $this->hasOne(RegisteredEquipment::class);
    }

    /**
     * @param  Builder<Equipment>  $query
     * @return Builder<Equipment>
     */
    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        return $query->where('user_id', $user->id);
    }
}
