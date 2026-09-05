<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MarkAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status_kehadiran' => ['required', 'string', 'in:hadir,tidak_hadir'],
        ];
    }

    public function messages(): array
    {
        return [
            'status_kehadiran.required' => 'Status kehadiran wajib dipilih.',
            'status_kehadiran.in' => 'Status kehadiran harus hadir atau tidak hadir.',
        ];
    }
}
