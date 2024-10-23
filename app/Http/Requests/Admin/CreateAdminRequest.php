<?php

namespace App\Http\Requests\Admin;

use App\Helpers\ResponseJson;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateAdminRequest extends FormRequest
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
            'nama' => 'required|max:100',
            'email' => 'required|email|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama perlu diisi.',
            'nama.max' => 'Nama tidak boleh lebih dari 100 karakter.',
            'email.required' => 'Email perlu diisi.',
            'email.email' => 'Email harus dalam format yang valid.',
            'email.max' => 'Email tidak boleh lebih dari 100 karakter.',
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
