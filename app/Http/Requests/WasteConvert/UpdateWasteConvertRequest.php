<?php

namespace App\Http\Requests\WasteConvert;

use App\Helpers\ResponseJson;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateWasteConvertRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'point' => 'numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'point.numeric' => 'Point must be a number',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();

        foreach ($errors as $field => $message) {
            throw new HttpResponseException(ResponseJson::validationErrorResponse("field error", [$field => $message[0]]));
        }
    }
}
