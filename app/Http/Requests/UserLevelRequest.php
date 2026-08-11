<?php

namespace App\Http\Requests;

use Database\Seeders\ModuleSeeder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserLevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userLevelId = $this->route('userLevel')?->id;

        return [
            'nama' => [
                'required',
                'string',
                'max:100',
                Rule::unique('panritta_user_levels', 'nama')->ignore($userLevelId),
            ],
            'keterangan' => [
                'nullable',
                'string',
                'max:255',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
            'permissions' => [
                'nullable',
                'array',
            ],
            'permissions.*' => [
                'string',
                Rule::exists('permissions', 'name')->where('guard_name', 'web'),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * Build a map of module key => granted actions from the flat permission names.
     *
     * @return array<string, list<string>>
     */
    public function moduleActions(): array
    {
        $actions = ModuleSeeder::ACTIONS;
        $map = [];

        foreach ((array) $this->input('permissions', []) as $permissionName) {
            if (! str_contains($permissionName, '.')) {
                continue;
            }

            [$moduleKey, $action] = explode('.', $permissionName, 2);

            if (! in_array($action, $actions, true)) {
                continue;
            }

            $map[$moduleKey][] = $action;
        }

        return $map;
    }

    /**
     * @return array<string, mixed>
     */
    public function levelAttributes(): array
    {
        return [
            'nama' => $this->input('nama'),
            'slug' => Str::slug($this->input('nama')),
            'keterangan' => $this->input('keterangan'),
            'is_active' => $this->boolean('is_active'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama level wajib diisi.',
            'nama.max' => 'Nama level maksimal 100 karakter.',
            'nama.unique' => 'Nama level sudah digunakan.',
            'permissions.*.exists' => 'Permission yang dipilih tidak valid.',
        ];
    }
}
