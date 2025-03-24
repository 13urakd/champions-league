<?php

namespace App\Http\Requests;

use App\Libraries\FormRequest;

class EventModifyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'simulationId' => [
                'required',
                'int',
                'min:1',
            ],
            'goalTeam1' => [
                'required',
                'int',
                'min:0',
                'max:150',
            ],
            'goalTeam2' => [
                'required',
                'int',
                'min:0',
                'max:150',
            ],
        ];
    }
}
