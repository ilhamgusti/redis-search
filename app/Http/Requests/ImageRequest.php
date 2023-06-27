<?php

namespace App\Http\Requests;

use App\Rules\UniquePosition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ImageRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $imageId = $this->route('image'); // Mengambil ID gambar jika ada
        return [
            'position' => [
                'required',
                'numeric',
                'min:1',
                'max:10',
                new UniquePosition($this->type, $this->is_desktop, $this->is_mobile, $imageId)
            ],
            'type' => [
                'required',
                Rule::in(['header', 'bawah']),
            ],
            'is_desktop' => 'required|boolean',
            'is_mobile' => 'required|boolean',
            'image' => 'required|image|mimes:jpeg,png|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'position.unique_position' => 'The selected position is already taken for the given type, desktop, and mobile combination.',
            'type.required' => 'The type field is required.',
            'type.in' => 'The type field must be either "header" or "bawah".',
            'is_desktop.required' => 'The is_desktop field is required.',
            'is_desktop.boolean' => 'The is_desktop field must be a boolean value.',
            'is_mobile.required' => 'The is_mobile field is required.',
            'is_mobile.boolean' => 'The is_mobile field must be a boolean value.',
            'image.required' => 'The image field is required.',
            'image.image' => 'The image must be a valid image file.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg.',
            'image.max' => 'The image may not be greater than :max kilobytes.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $validator->errors(),
        ]));
    }
}
