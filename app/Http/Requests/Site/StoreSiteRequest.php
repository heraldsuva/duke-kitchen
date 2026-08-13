<?php

namespace App\Http\Requests\Site;

use App\Models\Site;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Site::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'description' => ['required', 'string', 'max:255'],
            'active' => ['required', 'boolean'],
        ];

        if ($this->user()?->isSuperAdmin()) {
            $rules['user_id'] = ['required', 'integer', Rule::exists('users', 'id')];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('active')) {
            $this->merge([
                'active' => filter_var($this->input('active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            ]);
        }
    }
}
