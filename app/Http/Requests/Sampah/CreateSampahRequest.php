<?php

namespace App\Http\Requests\Sampah;

use App\Helpers\ResponseJson;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateSampahRequest extends FormRequest
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
            'Nama_Sampah' => 'required|max:100',
            'Berat_Sampah' => 'required|numeric',
            'Point' => 'required|numeric',
            // 'id_jenis' => 'required|numeric',
            // 'id_penjemputan' => 'required|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'Nama_Sampah.required' => 'Nama Sampah perlu diisi.',
            'Nama_Sampah.max' => 'Nama Sampah tidak boleh lebih dari 100 karakter.',
            'Berat_Sampah.required' => 'Berat Sampah perlu diisi.',
            'Berat_Sampah.numeric' => 'Berat Sampah harus berupa angka.',
            'Point.required' => 'Point perlu diisi.',
            'Point.numeric' => 'Point harus berupa angka.',
            // 'id_jenis.required' => 'Jenis Sampah perlu diisi.',
            // 'id_jenis.numeric' => 'Jenis Sampah harus berupa angka.',
            // 'id_penjemputan.required' => 'Penjemputan Sampah perlu diisi.',
            // 'id_penjemputan.numeric' => 'Penjemputan Sampah harus berupa angka.',
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
