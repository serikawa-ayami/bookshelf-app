<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\StoreGenreRequest;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class GenreValidationTest extends TestCase
{
    use RefreshDatabase;

    private function validate(array $data): bool
    {
        $request = new StoreGenreRequest();

        return Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        )->passes();
    }

    public function test_valid_genre_name_passes_validation(): void
    {
        $this->assertTrue(
            $this->validate([
                'name' => 'プログラミング',
            ])
        );
    }

    public function test_genre_name_is_required(): void
    {
        $this->assertFalse(
            $this->validate([
                'name' => '',
            ])
        );
    }

    public function test_genre_name_at_255_characters_is_valid(): void
    {
        $this->assertTrue(
            $this->validate([
                'name' => str_repeat('あ', 255),
            ])
        );
    }

    public function test_genre_name_over_255_characters_is_invalid(): void
    {
        $this->assertFalse(
            $this->validate([
                'name' => str_repeat('あ', 256),
            ])
        );
    }

    public function test_existing_genre_name_is_invalid(): void
    {
        Genre::create([
            'name' => 'プログラミング',
        ]);

        $this->assertFalse(
            $this->validate([
                'name' => 'プログラミング',
            ])
        );
    }

    public function test_fullwidth_and_halfwidth_normalized_same_name_is_invalid(): void
    {
        Genre::create([
            'name' => 'プログラミング',
        ]);

        $this->assertFalse(
            $this->validate([
                'name' => 'ﾌﾟﾛｸﾞﾗﾐﾝｸﾞ',
            ])
        );
    }
}