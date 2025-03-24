<?php

namespace App\Http\Requests;

use App\Libraries\FormRequest;

class FixtureRequest extends FormRequest
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
                'nullable',
                'int',
                'min:1'
            ],
            'reset' => [
                'nullable',
                'int',
                'min:0',
                'max:1',
            ],
        ];
    }

    // Sample to edit validation error message for this specific Request
    //    public function messages(): array
    //    {
    //        $messages = [];
    //        $messages['min']['numeric'] = ':attribute EN AZ :min olmalı.';
    //        $messages['integer'] = ':attribute TAM SAYI olmalı ';
    //
    //        return array_merge(
    //            parent::messages(),
    //            $messages
    //        );
    //    }

}
