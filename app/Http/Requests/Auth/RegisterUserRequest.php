<?php

namespace App\Http\Requests\Auth;

use App\Helpers\ResponseJson;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterUserRequest extends FormRequest
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
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6|max:32',
        ];
    }


    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.max' => 'Nama Maksimal 100 Karakter',
            'name.required' => 'Nama harus diisi',
            'email.required' => 'Email harus diisi',
            'email.max' => 'Email Maksimal 100 Karakter',
            'email.email' => 'Email tidak valid',
            'email.unique' => 'Email sudah terdaftar silahkan login',
            'password.required' => 'Password Harus diisi',
            'password.min' => 'Password Minimal 6 Karakter',
            'password.max' => 'Password Maksimal 32 Karakter',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();

        foreach ($errors as $field => $message) {
            throw new HttpResponseException(ResponseJson::failedResponse("field error", [$field => $message[0]]));
        }
    }
}
