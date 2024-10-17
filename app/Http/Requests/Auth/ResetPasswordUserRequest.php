<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helpers\ResponseJson;


class ResetPasswordUserRequest extends FormRequest
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
            'password' => 'required|min:6|max:32',
            'confirm_password' => 'required|same:password',
            'reset_pass_token' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'password.required' => 'Password Harus Diisi',
            'password.min'      => 'Password Minimal 6 Karakter',
            'password.max'      => 'Password Maksimal 12 Karakter',
            'confirm_password.required'   => 'Konfirmasi Password Harus Diisi',
            'confirm_password.same'   => 'Konfirmasi Password Harus Sama Dengan Password',
            'reset_pass_token.required'   => 'Token Tidak Valid',
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
