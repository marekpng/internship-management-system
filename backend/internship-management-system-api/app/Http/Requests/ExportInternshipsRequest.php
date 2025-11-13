<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExportInternshipsRequest extends FormRequest
{
    public function authorize(): bool
    {
        // route je už chránená middleware-om 'auth:api' + 'role:garant'
        return true;
    }

    public function rules(): array
    {
        return [
            'company' => ['sometimes', 'string'],
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
            'status' => ['sometimes', 'string', 'max:255'],
            'year' => ['sometimes', 'integer'],
            'semester' => ['sometimes', 'string', 'max:10'],
            'student' => ['sometimes', 'string'],
            'supervisor' => ['sometimes', 'string'],
        ];
    }
}
