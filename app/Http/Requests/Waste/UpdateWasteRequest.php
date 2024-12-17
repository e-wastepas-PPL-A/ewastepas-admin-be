<?php

namespace App\Http\Requests\Waste;

use App\Helpers\ResponseJson;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateWasteRequest extends FormRequest
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
            'waste_name' => 'max:100',
            'point' => 'numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'max:255',
            'waste_type_id' => 'numeric',
            'pickup_id' => 'numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'waste_name.max' => 'Waste name max 100 characters',
            'point.numeric' => 'Point must be a number',
            'image.image' => 'Image must be an image',
            'image.mimes' => 'Image must be jpeg, png, jpg, gif, svg',
            'image.max' => 'Image max 2048 KB',
            'description.max' => 'Description max 255 characters',
            'waste_type_id.numeric' => 'Waste type must be a number',
            'pickup_id.numeric' => 'Pickup id must be a number',
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
