<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => [
                'nullable',
                'string',
                'max:100',
            ],

            'brand' => [
                'nullable',
                'string',
                'max:100',
            ],

            'min_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'max_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'rating' => [
                'nullable',
                'numeric',
                'min:0',
                'max:5',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],

            'keyword' => [
                'nullable',
                'string',
                'max:255',
            ],

            'sort' => [
                'nullable',
                Rule::in([
                    'price_asc',
                    'price_desc',
                    'rating_desc',
                    'created_at_desc',
                ]),
            ],
            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'limit' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ];
    }
}
