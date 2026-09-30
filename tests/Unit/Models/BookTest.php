<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

class BookTest extends TestCase
{
    public function test_user_relationship_is_belongs_to(): void
    {
        $book = new Book;

        $this->assertInstanceOf(
            BelongsTo::class,
            $book->user()
        );
    }

    public function test_genres_relationship_is_belongs_to_many(): void
    {
        $book = new Book;

        $this->assertInstanceOf(
            BelongsToMany::class,
            $book->genres()
        );
    }

    public function test_reviews_relationship_is_has_many(): void
    {
        $book = new Book;

        $this->assertInstanceOf(
            HasMany::class,
            $book->reviews()
        );
    }

    public function test_favorites_relationship_is_has_many(): void
    {
        $book = new Book;

        $this->assertInstanceOf(
            HasMany::class,
            $book->favorites()
        );
    }

    public function test_fillable_attributes_are_defined(): void
    {
        $book = new Book;

        $this->assertSame(
            [
                'user_id',
                'title',
                'author',
                'isbn',
                'published_date',
                'description',
                'image_url',
            ],
            $book->getFillable()
        );
    }
}
