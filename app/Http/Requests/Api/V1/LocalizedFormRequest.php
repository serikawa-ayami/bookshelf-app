<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

abstract class LocalizedFormRequest extends FormRequest
{
    /**
     * Get validation messages from the corresponding language file.
     */
    public function messages(): array
    {
        $fileName = str_replace('Request', '', class_basename($this));
        $path = base_path("lang/ja/api/v1/{$fileName}.php");

        if (! file_exists($path)) {
            return [];
        }

        return require $path;
    }
}
