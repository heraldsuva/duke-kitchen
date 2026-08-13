<?php

namespace App\Http\Requests\Site;

use App\Models\Site;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Site $site */
        $site = $this->route('site');

        return $this->user()?->can('update', $site) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:255'],
            'active' => ['required', 'boolean'],
        ];
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
