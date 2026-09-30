<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_relationship_is_belongs_to(): void
    {
        $favorite = new Favorite;

        $this->assertInstanceOf(
            BelongsTo::class,
            $favorite->user()
        );
    }

    public function test_book_relationship_is_belongs_to(): void
    {
        $favorite = new Favorite;

        $this->assertInstanceOf(
            BelongsTo::class,
            $favorite->book()
        );
    }

    public function test_fillable_attributes_are_defined(): void
    {
        $favorite = new Favorite;

        $this->assertSame(
            [
                'user_id',
                'book_id',
            ],
            $favorite->getFillable()
        );
    }

    public function test_same_user_and_book_combination_cannot_be_duplicated(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'favorite-test@example.com',
            'password' => 'password',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => 'テスト用の書籍です。',
            'image_url' => null,
        ]);

        Favorite::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->expectException(QueryException::class);

        Favorite::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }
}
