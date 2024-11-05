<?php

namespace App\Http\Requests\Waste;

use App\Helpers\ResponseJson;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateWasteRequest extends FormRequest
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
            'waste_name' => 'required|max:100',
            'point' => 'required|numeric',
            'image' => 'nullable',
            'description' => 'required|max:255',
            'waste_type_id' => 'required|numeric',
            // 'pickup_id' => 'required|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'waste_name.required' => 'Waste name is required',
            'waste_name.max' => 'Waste name max 100 characters',
            'point.required' => 'Point is required',
            'point.numeric' => 'Point must be a number',
            'image.required' => 'Image is required',
            'description.required' => 'Description is required',
            'description.max' => 'Description max 255 characters',
            'waste_type_id.required' => 'Waste type is required',
            'waste_type_id.numeric' => 'Waste type must be a number',
            // 'pickup_id.required' => 'Pickup id is required',
            // 'pickup_id.numeric' => 'Pickup id must be a number',
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
