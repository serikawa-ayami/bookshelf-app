<?php

namespace Tests\Unit\Models;

use App\Models\Review;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    public function test_user_relationship_is_belongs_to(): void
    {
        $review = new Review;

        $this->assertInstanceOf(
            BelongsTo::class,
            $review->user()
        );
    }

    public function test_book_relationship_is_belongs_to(): void
    {
        $review = new Review;

        $this->assertInstanceOf(
            BelongsTo::class,
            $review->book()
        );
    }

    public function test_review_likes_relationship_is_has_many(): void
    {
        $review = new Review;

        $this->assertInstanceOf(
            HasMany::class,
            $review->reviewLikes()
        );
    }

    public function test_liked_by_users_relationship_is_belongs_to_many(): void
    {
        $review = new Review;

        $this->assertInstanceOf(
            BelongsToMany::class,
            $review->likedByUsers()
        );
    }

    public function test_fillable_attributes_are_defined(): void
    {
        $review = new Review;

        $this->assertSame(
            [
                'user_id',
                'book_id',
                'rating',
                'comment',
            ],
            $review->getFillable()
        );
    }
}
