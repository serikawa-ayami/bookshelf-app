<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * No.21
     * 有効なname/email/password/password_confirmationで登録し、
     * ユーザーが作成され、書籍一覧へ遷移し、
     * 「会員登録が完了しました。」が表示されること。
     */
    public function test_user_can_register_successfully(): void
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'register@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/');

        $response->assertSessionHas(
            'success',
            '会員登録が完了しました。'
        );

        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'register@example.com',
        ]);

        $user = User::where('email', 'register@example.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue(
            Hash::check('password', $user->password)
        );
    }

    /**
     * No.22
     * 不正な入力で登録送信をする。
     * 同じ登録画面へ戻り、入力値を保持して、
     * 日本語のバリデーションメッセージを表示すること。
     */
    public function test_registration_validation_error_returns_to_register_form(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => '',
            'email' => 'invalid-test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/register');

        $response->assertSessionHasErrors([
            'name' => '名前は必須です。',
        ]);

        $response->assertSessionHasInput([
            'email' => 'invalid-test@example.com',
        ]);

        $this->assertDatabaseMissing('users', [
            'email' => 'invalid-test@example.com',
        ]);
    }

    /**
     * No.23
     * 登録済みユーザーの正しいemail/passwordでログインし、
     * 認証され、書籍一覧へ遷移し、
     * 「ログインしました。」が表示されること。
     */
    public function test_registered_user_can_login_successfully(): void
    {
        $user = User::create([
            'name' => 'ログインテストユーザー',
            'email' => 'login@example.com',
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'email' => 'login@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/');

        $response->assertSessionHas(
            'success',
            'ログインしました。'
        );

        $this->assertAuthenticatedAs($user);
    }

    /**
     * No.24
     * 誤った認証情報でログインする。
     * ログインに失敗し、ログイン画面に留まる／戻り、
     * 認証エラーとなること。
     */
    public function test_login_fails_with_invalid_credentials(): void
    {
        User::create([
            'name' => 'ログイン失敗テストユーザー',
            'email' => 'login-fail@example.com',
            'password' => 'password',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'login-fail@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');

        $response->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    /**
     * No.25
     * ログイン済みユーザーがPOST /logoutし、
     * セッションが破棄され、ログイン画面へリダイレクトされること。
     */
    public function test_authenticated_user_can_logout(): void
    {
        $user = User::create([
            'name' => 'ログアウトテストユーザー',
            'email' => 'logout@example.com',
            'password' => 'password',
        ]);

        $this->actingAs($user);

        $this->assertAuthenticatedAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect('/login');

        $this->assertGuest();
    }
}