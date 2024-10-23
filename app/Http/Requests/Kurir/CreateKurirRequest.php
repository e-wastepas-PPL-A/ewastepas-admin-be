<?php

namespace App\Http\Requests\Kurir;

use App\Helpers\ResponseJson;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateKurirRequest extends FormRequest
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
            'Nama' => 'required|max:100',
            'Email' => 'required|email|max:100',
            'No_Telp' => 'required|numeric',
            'NIK' => 'sometimes|numeric|digits:16',
            'No_Rek' => 'sometimes|numeric',
            // 'KTP_URL' => 'sometimes|url',
            // 'KK_URL' => 'sometimes|url',
        ];
    }

    public function messages(): array
    {
        return [
            'Nama.required' => 'Nama perlu diisi.',
            'Nama.max' => 'Nama tidak boleh lebih dari 100 karakter.',
            'Email.required' => 'Email perlu diisi.',
            'Email.email' => 'Email harus dalam format yang valid.',
            'Email.max' => 'Email tidak boleh lebih dari 100 karakter.',
            'No_Telp.required' => 'Nomor telepon perlu diisi.',
            'No_Telp.numeric' => 'Nomor telepon harus berupa angka.',
            'NIK.required' => 'NIK perlu diisi.',
            'NIK.numeric' => 'NIK harus berupa angka.',
            'NIK.digits' => 'NIK harus 16 digit.',
            'No_Rek.required' => 'Nomor rekening perlu diisi.',
            'No_Rek.numeric' => 'Nomor rekening harus berupa angka.',
            // 'KTP_URL.required' => 'URL KTP perlu diisi.',
            // 'KTP_URL.url' => 'URL KTP tidak valid.',
            // 'KK_URL.required' => 'URL KK perlu diisi.',
            // 'KK_URL.url' => 'URL KK tidak valid.',
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
