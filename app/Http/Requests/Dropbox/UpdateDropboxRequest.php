<?php

namespace App\Http\Requests\Dropbox;

use App\Helpers\ResponseJson;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateDropboxRequest extends FormRequest
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
            'district_address' => 'required|max:255|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'capacity' => 'required|numeric',
            'status' => 'required|in:Avaliable,Full',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required',
            'name.string' => 'Name must be string',
            'name.max' => 'Name is too long',
            'address.required' => 'Address is required',
            'address.string' => 'Address must be string',
            'address.max' => 'Address is too long',
            'district_address.required' => 'District Address is required',
            'district_address.string' => 'District Address must be string',
            'district_address.max' => 'District Address is too long',
            'latitude.required' => 'Latitude is required',
            'latitude.numeric' => 'Latitude must be numeric',
            'longitude.required' => 'Longitude is required',
            'longitude.numeric' => 'Longitude must be numeric',
            'capacity.required' => 'Capacity is required',
            'capacity.numeric' => 'Capacity must be numeric',
            'status.required' => 'Status is required',
            'status.string' => 'Status must be string',
            'status.in' => 'Status must be Avaliable or Full',
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
