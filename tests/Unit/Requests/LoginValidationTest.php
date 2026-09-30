<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class LoginValidationTest extends TestCase
{
    private function validate(array $data): bool
    {
        $request = new LoginRequest;

        return Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        )->passes();
    }

    public function test_valid_login_input_passes_validation(): void
    {
        $this->assertTrue(
            $this->validate([
                'email' => 'test@example.com',
                'password' => 'password',
            ])
        );
    }

    public function test_email_is_required(): void
    {
        $this->assertFalse(
            $this->validate([
                'email' => '',
                'password' => 'password',
            ])
        );
    }

    public function test_email_format_is_invalid(): void
    {
        $this->assertFalse(
            $this->validate([
                'email' => 'invalid-email',
                'password' => 'password',
            ])
        );
    }

    public function test_email_must_be_string(): void
    {
        $this->assertFalse(
            $this->validate([
                'email' => 12345,
                'password' => 'password',
            ])
        );
    }

    public function test_password_is_required(): void
    {
        $this->assertFalse(
            $this->validate([
                'email' => 'test@example.com',
                'password' => '',
            ])
        );
    }

    public function test_password_must_be_string(): void
    {
        $this->assertFalse(
            $this->validate([
                'email' => 'test@example.com',
                'password' => 12345,
            ])
        );
    }
}
