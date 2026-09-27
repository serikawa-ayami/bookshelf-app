<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Review;
use App\Models\ReviewLike;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use Tests\TestCase;

class ReviewLikeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_relationship_is_belongs_to(): void
    {
        $reviewLike = new ReviewLike();

        $this->assertInstanceOf(
            BelongsTo::class,
            $reviewLike->user()
        );
    }

    public function test_review_relationship_is_belongs_to(): void
    {
        $reviewLike = new ReviewLike();

        $this->assertInstanceOf(
            BelongsTo::class,
            $reviewLike->review()
        );
    }

    public function test_fillable_attributes_are_defined(): void
    {
        $reviewLike = new ReviewLike();

        $this->assertSame(
            [
                'user_id',
                'review_id',
            ],
            $reviewLike->getFillable()
        );
    }

    public function test_same_user_and_review_combination_cannot_be_duplicated(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'review-like-test@example.com',
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

        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'テストレビューです。',
        ]);

        ReviewLike::create([
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        $this->expectException(QueryException::class);

        ReviewLike::create([
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }
}
