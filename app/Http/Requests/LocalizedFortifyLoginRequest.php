<?php

namespace App\Http\Requests;

use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;

abstract class LocalizedFortifyLoginRequest extends FortifyLoginRequest
{
    /**
     * Get validation messages from the corresponding language file.
     */
    public function messages(): array
    {
        $fileName = str_replace('Request', '', class_basename($this));
        $path = base_path("lang/ja/{$fileName}.php");

        if (! file_exists($path)) {
            return [];
        }

        return require $path;
    }
}
