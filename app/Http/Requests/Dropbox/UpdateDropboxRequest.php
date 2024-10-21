<?php

namespace App\Http\Requests\Dropbox;

use App\Helpers\ResponseJson;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateDropboxRequest extends FormRequest
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
            'LocationName' => 'required|max:100',
            'Address' => 'required|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'LocationName.required' => 'Location Name perlu diisi.',
            'LocationName.max' => 'Location Name tidak boleh lebih dari 100 karakter.',
            'Address.required' => 'Address perlu diisi.',
            'Address.max' => 'Address tidak boleh lebih dari 100 karakter.',
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
