<?php

namespace App\Http\Requests\Product;

use App\Helpers\ResponseJson;
use App\Helpers\Sanitizer;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateProductRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'links' => ['sometimes', 'array'],
            'links.*' => ['url', 'not_shortlink'],
            'title' => 'required|max:40',
            'price' => 'sometimes',
            'category' => 'required:max:100',
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg|max:3072',
            'is_active' => 'required|in:1,0'
        ];
    }
    public function messages(): array
    {
        return [
            'links.url' => 's harus berupa URL yang valid.',
            'links.not_shortlink' => 'Untuk menjaga kenyamanan pengunjung toko anda kami tidak mengizinkan link yang berasal dari domain shortlink.',
            'title.required' => 'Text tautan perlu diisi.',
            'title.max' => 'Text tautan tidak boleh lebih dari 40 karakter.',
            'category.required' => 'Kategori perlu diisi.',
            'category.max' => 'Kategori tidak boleh lebih dari 100 karakter.',
            'thumbnail.required' => 'Silahkan upload Foto Produk',
            'thumbnail.image' => 'Foto Produk Harus Berupa Gambar',
            'thumbnail.mimes' => 'Foto Produk Harus Berupa File Dengan Format: jpeg, png, jpg',
            'thumbnail.max' => 'Foto Produk Maksimal 3MB',
            'is_active.required' => 'Status perlu diisi.',
            'is_active.in' => 'Status tidak valid',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();

        foreach ($errors as $field => $message) {
            throw new HttpResponseException(ResponseJson::validationErrorResponse("field error", [$field => $message[0]]));
        }
    }
    public function withValidator($validator)
    {
        $validator->addImplicitExtension('not_shortlink', function ($attribute, $value, $parameters, $validator) {
            return CreateProductRequest::isForbiddenShortlink($value);
        });
    }

    // Fungsi untuk memeriksa apakah domain shortlink ada dalam URL
    public static function isForbiddenShortlink($url)
    {
        return Sanitizer::forbiddenShortlink($url);
    }
}
