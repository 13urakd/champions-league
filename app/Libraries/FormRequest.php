<?php

namespace App\Libraries;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest as DefaultFormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Validator as ValidationBase;

abstract class FormRequest extends DefaultFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    abstract public function rules();

    public function messages(): array
    {
        $messages = trans('validation');

        // Sample to edit validation error message for the system
        //    $messages['min']['numeric'] = ':attribute en az :min olmalı.';
        //    $messages['integer'] = ':attribute tam sayı olmalı ';

        return $messages;
    }

    protected function failedValidation(Validator|ValidationBase $validator)
    {
        $messages = [];

        $validatorMessages = $validator->errors()->getMessages();
        foreach ($validator->failed() as $inputKey => $rules) {
            foreach ($rules as $ruleKey => $ruleValues) {
                $messages[] = [
                    'code' => '_VALIDATION_' . strtoupper($ruleKey),
                    'message' => array_shift($validatorMessages[$inputKey]),
                    'key' => $inputKey,
                    'ruleValues' => $ruleValues,
                ];
            }
        }

        throw new HttpResponseException(
            Response::error('VALIDATION_FAILS', null, $messages)
        );
    }


}
