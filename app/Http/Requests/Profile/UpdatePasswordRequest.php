<?php

namespace App\Http\Requests\Profile;

use App\Helpers\ResponseJson;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'password' => [
                'required',
                'min:6',
                'max:32',
                // function ($attribute, $value, $fail) {
                //     // Custom validation rule for new_password
                //     if (!preg_match('/[0-9]/', $value)) {
                //         $fail('Password Baru harus mengandung setidaknya satu digit angka.');
                //     }
                //     if (!preg_match('/[a-z]/', $value)) {
                //         $fail('Password Baru harus mengandung setidaknya satu huruf kecil.');
                //     }
                //     if (!preg_match('/[A-Z]/', $value)) {
                //         $fail('Password Baru harus mengandung setidaknya satu huruf besar.');
                //     }
                //     if (!preg_match('/[\W_]/', $value)) {
                //         $fail('Password Baru harus mengandung setidaknya satu karakter khusus.');
                //     }
                // },
            ],
            'confirm_password' => 'same:password|max:32',
            'current_password' => 'required|max:32',
        ];
    }

    public function messages()
    {
        return [
            'password.required'         => 'Password Baru Harus Diisi',
            'current_password.required' => 'Password Lama Harus Diisi',
            'password.min'              => 'Password Minimal 6 Karakter',
            '*.max'                     => 'Password Maksimal 32 Karakter',
            'confirm_password.same'     => 'Konfirmasi Password Baru Harus Sama Dengan Password Baru',
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
