<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
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
            'query' => 'nullable|string|max:255',
            'type' => 'nullable|in:video,article',
            'sort' => 'nullable|in:relevance,date,popularity',
            'order' => 'nullable|in:asc,desc',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'query' => 'arama sorgusu',
            'type' => 'içerik tipi',
            'sort' => 'sıralama kriteri',
            'order' => 'sıralama düzeni',
            'page' => 'sayfa',
            'per_page' => 'sayfa başına içerik'
        ];
    }
}
