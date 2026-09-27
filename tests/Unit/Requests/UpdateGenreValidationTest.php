<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\UpdateGenreRequest;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateGenreValidationTest extends TestCase
{
    use RefreshDatabase;

    private function validate(Genre $genre, array $data): bool
    {
        $request = UpdateGenreRequest::create(
            '/genres/' . $genre->id,
            'PUT',
            $data
        );

        $request->setRouteResolver(function () use ($genre) {
            return new class ($genre) {
                public function __construct(
                private Genre $genre
                ) {
                }

                public function parameter($key = null, $default = null)
                {
                    if ($key === 'genre') {
                        return $this->genre;
                    }

                    return $default;
                }
            };
        });

        return Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        )->passes();
    }

    private function createGenre(string $name = 'プログラミング'): Genre
    {
        return Genre::create([
            'name' => $name,
        ]);
    }

    public function test_valid_genre_name_passes_validation(): void
    {
        $genre = $this->createGenre();

        $this->assertTrue(
            $this->validate($genre, [
                'name' => '更新後ジャンル',
            ])
        );
    }

    public function test_genre_name_is_required(): void
    {
        $genre = $this->createGenre();

        $this->assertFalse(
            $this->validate($genre, [
                'name' => '',
            ])
        );
    }

    public function test_genre_name_at_255_characters_is_valid(): void
    {
        $genre = $this->createGenre();

        $this->assertTrue(
            $this->validate($genre, [
                'name' => str_repeat('あ', 255),
            ])
        );
    }

    public function test_genre_name_over_255_characters_is_invalid(): void
    {
        $genre = $this->createGenre();

        $this->assertFalse(
            $this->validate($genre, [
                'name' => str_repeat('あ', 256),
            ])
        );
    }

    public function test_own_genre_name_is_allowed(): void
    {
        $genre = $this->createGenre('プログラミング');

        $this->assertTrue(
            $this->validate($genre, [
                'name' => 'プログラミング',
            ])
        );
    }

    public function test_name_used_by_another_genre_is_invalid(): void
    {
        $genre = $this->createGenre('プログラミング');

        $this->createGenre('小説');

        $this->assertFalse(
            $this->validate($genre, [
                'name' => '小説',
            ])
        );
    }
}