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
            'name' => 'required|max:100|string',
            'address' => 'required|max:255|string',
            'district_address' => 'required|max:255|string|in:Bandung Utara,Bandung Selatan,Bandung Barat,Bandung Timur,Cimahi,Kabupaten Bandung,Kabupaten Bandung Barat',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'capacity' => 'required|numeric|min:0'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required',
            'name.max' => 'Name max 100 characters',
            'name.string' => 'Name must be string',
            'address.required' => 'Address is required',
            'address.max' => 'Address max 255 characters',
            'address.string' => 'Address must be string',
            'district_address.required' => 'District Address is required',
            'district_address.max' => 'District Address max 255 characters',
            'district_address.string' => 'District Address must be string',
            'district_address.in' => 'District Address must be Bandung Utara, Bandung Selatan, Bandung Barat, Bandung Timur, Cimahi, Kabupaten Bandung, Kabupaten Bandung Barat',
            'latitude.required' => 'Latitude is required',
            'latitude.numeric' => 'Latitude must be numeric',
            'latitude.between' => 'Latitude must be between -90 and 90',
            'longitude.required' => 'Longitude is required',
            'longitude.numeric' => 'Longitude must be numeric',
            'longitude.between' => 'Longitude must be between -180 and 180',
            'capacity.required' => 'Capacity is required',
            'capacity.numeric' => 'Capacity must be numeric',
            'capacity.min' => 'Capacity must be greater than or equal to 0'
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
