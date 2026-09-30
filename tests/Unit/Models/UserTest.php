<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_books_relationship_is_has_many(): void
    {
        $user = new User;

        $this->assertInstanceOf(
            HasMany::class,
            $user->books()
        );
    }

    public function test_reviews_relationship_is_has_many(): void
    {
        $user = new User;

        $this->assertInstanceOf(
            HasMany::class,
            $user->reviews()
        );
    }

    public function test_favorites_relationship_is_has_many(): void
    {
        $user = new User;

        $this->assertInstanceOf(
            HasMany::class,
            $user->favorites()
        );
    }

    public function test_favorite_books_relationship_is_belongs_to_many(): void
    {
        $user = new User;

        $this->assertInstanceOf(
            BelongsToMany::class,
            $user->favoriteBooks()
        );
    }

    public function test_review_likes_relationship_is_has_many(): void
    {
        $user = new User;

        $this->assertInstanceOf(
            HasMany::class,
            $user->reviewLikes()
        );
    }

    public function test_liked_reviews_relationship_is_belongs_to_many(): void
    {
        $user = new User;

        $this->assertInstanceOf(
            BelongsToMany::class,
            $user->likedReviews()
        );
    }

    public function test_fillable_attributes_are_defined(): void
    {
        $user = new User;

        $this->assertSame(
            ['name', 'email', 'password'],
            $user->getFillable()
        );
    }

    public function test_hidden_attributes_are_defined(): void
    {
        $user = new User;

        $this->assertSame(
            ['password', 'remember_token'],
            $user->getHidden()
        );
    }

    public function test_email_verified_at_cast_is_datetime(): void
    {
        $user = new User;

        $this->assertSame(
            'datetime',
            $user->getCasts()['email_verified_at']
        );
    }

    public function test_password_cast_is_hashed(): void
    {
        $user = new User;

        $this->assertSame(
            'hashed',
            $user->getCasts()['password']
        );
    }
}
