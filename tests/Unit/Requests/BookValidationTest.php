<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\StoreBookRequest;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class BookValidationTest extends TestCase
{
    use RefreshDatabase;

    private function validate(array $data): bool
    {
        $request = new StoreBookRequest;

        return Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        )->passes();
    }

    private function validBookData(): array
    {
        $genre = Genre::create([
            'name' => 'テストジャンル',
        ]);

        return [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => 'テスト用の書籍です。',
            'image_url' => 'https://example.com/image.jpg',
            'genres' => [$genre->id],
        ];
    }

    public function test_valid_book_data_passes_validation(): void
    {
        $this->assertTrue(
            $this->validate($this->validBookData())
        );
    }

    public function test_title_is_required(): void
    {
        $data = $this->validBookData();
        $data['title'] = '';

        $this->assertFalse($this->validate($data));
    }

    public function test_title_at_255_characters_is_valid(): void
    {
        $data = $this->validBookData();
        $data['title'] = str_repeat('あ', 255);

        $this->assertTrue($this->validate($data));
    }

    public function test_title_over_255_characters_is_invalid(): void
    {
        $data = $this->validBookData();
        $data['title'] = str_repeat('あ', 256);

        $this->assertFalse($this->validate($data));
    }

    public function test_author_is_required(): void
    {
        $data = $this->validBookData();
        $data['author'] = '';

        $this->assertFalse($this->validate($data));
    }

    public function test_author_at_255_characters_is_valid(): void
    {
        $data = $this->validBookData();
        $data['author'] = str_repeat('あ', 255);

        $this->assertTrue($this->validate($data));
    }

    public function test_author_over_255_characters_is_invalid(): void
    {
        $data = $this->validBookData();
        $data['author'] = str_repeat('あ', 256);

        $this->assertFalse($this->validate($data));
    }

    public function test_isbn_is_required(): void
    {
        $data = $this->validBookData();
        $data['isbn'] = '';

        $this->assertFalse($this->validate($data));
    }

    public function test_isbn_with_13_digits_is_valid(): void
    {
        $data = $this->validBookData();
        $data['isbn'] = '1234567890123';

        $this->assertTrue($this->validate($data));
    }

    public function test_isbn_with_12_digits_is_invalid(): void
    {
        $data = $this->validBookData();
        $data['isbn'] = '123456789012';

        $this->assertFalse($this->validate($data));
    }

    public function test_isbn_with_14_digits_is_invalid(): void
    {
        $data = $this->validBookData();
        $data['isbn'] = '12345678901234';

        $this->assertFalse($this->validate($data));
    }

    public function test_duplicate_isbn_is_invalid(): void
    {
        $user = User::factory()->create();

        $genre = Genre::create([
            'name' => '既存ジャンル',
        ]);

        Book::create([
            'user_id' => $user->id,
            'title' => '既存書籍',
            'author' => '既存著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => null,
            'image_url' => null,
        ]);

        $data = [
            'title' => '新規書籍',
            'author' => '新規著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => null,
            'image_url' => null,
            'genres' => [$genre->id],
        ];

        $this->assertFalse($this->validate($data));
    }

    public function test_published_date_is_required(): void
    {
        $data = $this->validBookData();
        $data['published_date'] = '';

        $this->assertFalse($this->validate($data));
    }

    public function test_invalid_published_date_is_invalid(): void
    {
        $data = $this->validBookData();
        $data['published_date'] = 'invalid-date';

        $this->assertFalse($this->validate($data));
    }

    public function test_description_at_1000_characters_is_valid(): void
    {
        $data = $this->validBookData();
        $data['description'] = str_repeat('あ', 1000);

        $this->assertTrue($this->validate($data));
    }

    public function test_description_over_1000_characters_is_invalid(): void
    {
        $data = $this->validBookData();
        $data['description'] = str_repeat('あ', 1001);

        $this->assertFalse($this->validate($data));
    }

    public function test_description_can_be_null(): void
    {
        $data = $this->validBookData();
        $data['description'] = null;

        $this->assertTrue($this->validate($data));
    }

    public function test_image_url_is_valid(): void
    {
        $data = $this->validBookData();
        $data['image_url'] = 'https://example.com/image.jpg';

        $this->assertTrue($this->validate($data));
    }

    public function test_invalid_image_url_is_invalid(): void
    {
        $data = $this->validBookData();
        $data['image_url'] = 'invalid-url';

        $this->assertFalse($this->validate($data));
    }

    public function test_image_url_can_be_null(): void
    {
        $data = $this->validBookData();
        $data['image_url'] = null;

        $this->assertTrue($this->validate($data));
    }

    public function test_genres_are_required(): void
    {
        $data = $this->validBookData();
        $data['genres'] = [];

        $this->assertFalse($this->validate($data));
    }

    public function test_genres_must_be_array(): void
    {
        $data = $this->validBookData();
        $data['genres'] = '1';

        $this->assertFalse($this->validate($data));
    }

    public function test_genres_must_contain_existing_genre_ids(): void
    {
        $data = $this->validBookData();
        $data['genres'] = [99999];

        $this->assertFalse($this->validate($data));
    }
}
