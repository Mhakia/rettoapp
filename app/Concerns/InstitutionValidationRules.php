<?php

namespace App\Concerns;

use App\Models\Institution;
use Illuminate\Validation\Rule;

trait InstitutionValidationRules
{
    /**
     * Validation rules shared by institution creation and editing.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function institutionRules(?int $ignoreNitId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nit' => ['nullable', 'string', 'max:255', Rule::unique('institutions', 'nit')->ignore($ignoreNitId)],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'bulletin_frequency' => ['required', Rule::in(['weekly', 'biweekly', 'monthly', 'disabled'])],
            'contact_first_name' => ['required', 'string', 'max:255'],
            'contact_middle_name' => ['nullable', 'string', 'max:255'],
            'contact_last_name' => ['required', 'string', 'max:255'],
            'contact_second_last_name' => ['required', 'string', 'max:255'],
            'contact_document_type' => ['required', Rule::in(Institution::DOCUMENT_TYPES)],
            'contact_document_number' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:255'],
            'principal_name' => ['required', 'string', 'max:255'],
            'principal_document_type' => ['required', Rule::in(Institution::DOCUMENT_TYPES)],
            'principal_document_number' => ['required', 'string', 'max:255'],
            'principal_started_at' => ['nullable', 'date'],
            'country' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'entity_type' => ['required', Rule::in(['public', 'private'])],
        ];
    }
}
