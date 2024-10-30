<?php

namespace App\Http\Requests\Sampah;

use App\Helpers\ResponseJson;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateSampahRequest extends FormRequest
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
            'Nama_Sampah' => 'sometimes|max:100',
            'Berat_Sampah' => 'sometimes|numeric',
            'Point' => 'sometimes|numeric',
            'id_jenis' => 'sometimes|numeric',
            'id_penjemputan' => 'sometimes|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'Nama_Sampah.max' => 'Nama Sampah tidak boleh lebih dari 100 karakter.',
            'Berat_Sampah.numeric' => 'Berat Sampah harus berupa angka.',
            'Point.numeric' => 'Point harus berupa angka.',
            'id_jenis.numeric' => 'Jenis Sampah harus berupa angka.',
            'id_penjemputan.numeric' => 'Penjemputan Sampah harus berupa angka.',
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
