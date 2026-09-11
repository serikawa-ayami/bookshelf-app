<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Contracts\LoginResponse;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            \Laravel\Fortify\Http\Requests\LoginRequest::class,
            \App\Http\Requests\LoginRequest::class
        );

        $this->app->instance(
            \Laravel\Fortify\Contracts\LogoutResponse::class,
            new class implements \Laravel\Fortify\Contracts\LogoutResponse {
            public function toResponse($request)
            {
                return redirect('/login');
            }
            }
        );

        $this->app->instance(
            RegisterResponse::class,
            new class implements RegisterResponse {
            public function toResponse($request)
            {
                return redirect('/')
                    ->with('success', '会員登録が完了しました。');
            }
            }
        );

        $this->app->instance(
            LoginResponse::class,
            new class implements LoginResponse {
            public function toResponse($request)
            {
                return redirect('/')
                    ->with('success', 'ログインしました。');
            }
            }
        );
    }

    public function boot(): void
    {
        Fortify::loginView(fn() => view('auth.login'));
        Fortify::registerView(fn() => view('auth.register'));

        Fortify::createUsersUsing(CreateNewUser::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(
                Str::lower($request->input(Fortify::username())) . '|' . $request->ip()
            );

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}