<?php

namespace Tests\Unit\Requests;

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AuthValidationTest extends TestCase
{
    use RefreshDatabase;

    private CreateNewUser $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = new CreateNewUser;
    }

    public function test_valid_registration_input_can_create_user(): void
    {
        $user = $this->action->create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertInstanceOf(User::class, $user);

        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);
    }

    public function test_name_is_required(): void
    {
        $this->expectException(ValidationException::class);

        $this->action->create([
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
    }

    public function test_name_at_255_characters_is_valid(): void
    {
        $user = $this->action->create([
            'name' => str_repeat('あ', 255),
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_name_over_255_characters_is_invalid(): void
    {
        $this->expectException(ValidationException::class);

        $this->action->create([
            'name' => str_repeat('あ', 256),
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
    }

    public function test_email_is_required(): void
    {
        $this->expectException(ValidationException::class);

        $this->action->create([
            'name' => 'テストユーザー',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
    }

    public function test_email_format_is_invalid(): void
    {
        $this->expectException(ValidationException::class);

        $this->action->create([
            'name' => 'テストユーザー',
            'email' => 'invalid-email',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
    }

    public function test_duplicate_email_is_invalid(): void
    {
        User::create([
            'name' => '既存ユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->expectException(ValidationException::class);

        $this->action->create([
            'name' => '新規ユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
    }

    public function test_password_is_required(): void
    {
        $this->expectException(ValidationException::class);

        $this->action->create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);
    }

    public function test_password_confirmation_must_match(): void
    {
        $this->expectException(ValidationException::class);

        $this->action->create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'different-password',
        ]);
    }
}
