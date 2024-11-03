<?php

namespace App\Http\Requests\Community;

use App\Helpers\ResponseJson;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateCommunityRequest extends FormRequest
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
            'name' => 'required|max:100',
            'email' => 'required|email|max:100',
            'password' => 'required',
            'phone' => 'required|numeric',
            'date_of_birth' => 'required',
            'address' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required',
            'name.max' => 'Name max 100 characters',
            'email.required' => 'Email is required',
            'email.email' => 'Email is not valid',
            'email.max' => 'Email max 100 characters',
            'password.required' => 'Password is required',
            'phone.required' => 'Phone is required',
            'phone.numeric' => 'Phone must be numeric',
            'date_of_birth.required' => 'Date of birth is required',
            'address.required' => 'Address is required',
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
