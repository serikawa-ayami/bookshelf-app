<?php

namespace Tests\Unit\Models;

use App\Models\Genre;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Tests\TestCase;

class GenreTest extends TestCase
{
    public function test_books_relationship_is_belongs_to_many(): void
    {
        $genre = new Genre;

        $this->assertInstanceOf(
            BelongsToMany::class,
            $genre->books()
        );
    }

    public function test_fillable_attributes_are_defined(): void
    {
        $genre = new Genre;

        $this->assertSame(
            ['name'],
            $genre->getFillable()
        );
    }
}
