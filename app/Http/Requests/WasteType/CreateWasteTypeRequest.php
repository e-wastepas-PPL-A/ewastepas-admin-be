<?php

namespace App\Http\Requests\WasteType;

use App\Helpers\ResponseJson;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateWasteTypeRequest extends FormRequest
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
            'waste_type_name' => 'required|max:100',
            'image' => 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'waste_type_name.required' => 'Waste type name is required',
            'waste_type_name.max' => 'Waste type name max 100 characters',
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
