<?php

namespace App\Http\Requests\Community;

use App\Helpers\ResponseJson;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCommunityRequest extends FormRequest
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
            'name' => 'max:40',
            'email' => 'email|max:100',
            'password' => 'min:6',
            'phone' => 'numeric',
            'date_of_birth' => 'date',
            'address' => 'string',
            'account_number' => 'string',
            'nik' => 'string',
            'ktp_url' => 'string|url',
            'kk_url' => 'string|url',
            'photo' => 'string|url',
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'Name max 40 characters',
            'email.email' => 'Email is not valid',
            'email.max' => 'Email max 100 characters',
            'password.min' => 'Password min 6 characters',
            'phone.numeric' => 'Phone must be numeric',
            'date_of_birth.date' => 'Date of birth is not valid',
            'address.string' => 'Address must be string'
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
