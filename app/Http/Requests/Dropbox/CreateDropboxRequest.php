<?php

namespace App\Http\Requests\Dropbox;

use App\Helpers\ResponseJson;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateDropboxRequest extends FormRequest
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
            'alamat_dropbox' => 'required|max:100',
            'longitude' => 'required',
            'latitude' => 'required',
            // 'created_at' => 'required|date',
        ];
    }

    public function messages(): array
    {
        return [
            'alamat_dropbox.required' => 'Alamat Dropbox perlu diisi.',
            'alamat_dropbox.max' => 'Alamat Dropbox maksimal 100 karakter.',
            'longitude.required' => 'Longitude perlu diisi.',
            'latitude.required' => 'Latitude perlu diisi.',
            // 'created_at.required' => 'Created At perlu diisi.',
            // 'created_at.date' => 'Created At harus dalam format tanggal yang valid.',
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
