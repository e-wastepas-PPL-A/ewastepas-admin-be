<?php

namespace App\Http\Requests\Kurir;

use App\Helpers\ResponseJson;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateKurirRequest extends FormRequest
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
            'name' => 'sometimes|max:40', // Ganti 'title' dengan 'name' jika sesuai
            'email' => 'sometimes|email|max:100', // Tambahkan atau ubah sesuai dengan kebutuhan admin
            'password' => 'sometimes|nullable|min:6', // Menambahkan validasi untuk password jika perlu
            // Tambahkan aturan lain jika diperlukan
        ];
    }

    public function messages(): array
    {
        return [
            // 'name.required' => 'Nama perlu diisi.',
            'name.max' => 'Nama tidak boleh lebih dari 40 karakter.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email tidak boleh lebih dari 100 karakter.',
            'password.min' => 'Password minimal 6 karakter.',
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
