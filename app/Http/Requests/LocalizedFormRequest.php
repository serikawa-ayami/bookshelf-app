<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class LocalizedFormRequest extends FormRequest
{
    /**
     * Get validation messages from the corresponding language file.
     */
    protected function localizedMessages(): array
    {
        $fileName = str_replace('Request', '', class_basename($this));
        $path = base_path("lang/ja/{$fileName}.php");

        if (!file_exists($path)) {
            return [];
        }

        return require $path;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return $this->localizedMessages();
    }
}
