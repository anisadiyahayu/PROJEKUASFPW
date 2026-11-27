<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(User::ROLE_ADMIN) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $ship = $this->route('ship');

        return [
            'owner_id' => [
                'required',
                'integer',
                Rule::exists('owners', 'id')->whereNull('deleted_at'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'hull_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ships', 'hull_number')->ignore($ship),
            ],
            'jenis_kapal' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'owner_id' => 'pemilik kapal',
            'name' => 'nama kapal',
            'hull_number' => 'nomor lambung',
            'jenis_kapal' => 'jenis kapal',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'owner_id.exists' => 'Pemilik yang dipilih tidak tersedia atau sudah dihapus.',
            'hull_number.unique' => 'Nomor lambung sudah dipakai kapal lain.',
        ];
    }
}
