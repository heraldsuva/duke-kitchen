<?php

namespace App\Http\Requests\Equipment;

use App\Enums\EquipmentCondition;
use App\Models\Equipment;
use App\Models\Site;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Equipment::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = $this->user();

        return [
            'serial_number' => ['required', 'string', 'max:100', 'unique:equipment,serial_number'],
            'description' => ['required', 'string', 'max:255'],
            'condition' => ['required', Rule::enum(EquipmentCondition::class)],
            'site_id' => [
                'nullable',
                'integer',
                Rule::exists('sites', 'id'),
                function (string $attribute, mixed $value, \Closure $fail) use ($user): void {
                    if ($value === null || $user === null) {
                        return;
                    }

                    $site = Site::query()->find($value);

                    if ($site === null) {
                        return;
                    }

                    if (! $user->isSuperAdmin() && $site->user_id !== $user->id) {
                        $fail('This site belongs to another user.');
                    }
                },
            ],
        ];
    }
}
