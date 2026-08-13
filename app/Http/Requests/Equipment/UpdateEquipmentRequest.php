<?php

namespace App\Http\Requests\Equipment;

use App\Enums\EquipmentCondition;
use App\Models\Equipment;
use App\Models\Site;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Equipment $equipment */
        $equipment = $this->route('equipment');

        return $this->user()?->can('update', $equipment) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Equipment $equipment */
        $equipment = $this->route('equipment');
        $user = $this->user();

        return [
            'serial_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('equipment', 'serial_number')->ignore($equipment->id),
            ],
            'description' => ['required', 'string', 'max:255'],
            'condition' => ['required', Rule::enum(EquipmentCondition::class)],
            'site_id' => [
                'nullable',
                'integer',
                Rule::exists('sites', 'id'),
                function (string $attribute, mixed $value, \Closure $fail) use ($user, $equipment): void {
                    if ($value === null || $user === null) {
                        return;
                    }

                    $site = Site::query()->find($value);

                    if ($site === null) {
                        return;
                    }

                    if (! $user->isSuperAdmin() && $site->user_id !== $user->id) {
                        $fail('This site belongs to another user.');

                        return;
                    }

                    if ($site->user_id !== $equipment->user_id) {
                        $fail('Equipment and site must belong to the same user.');
                    }
                },
            ],
        ];
    }
}
