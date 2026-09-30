<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateBookValidationTest extends TestCase
{
    use RefreshDatabase;

    private function validate(Book $book, array $data): bool
    {
        $request = UpdateBookRequest::create(
            '/books/'.$book->id,
            'PUT',
            $data
        );

        $request->setRouteResolver(function () use ($book) {
            return new class($book)
            {
                public function __construct(
                    private Book $book
                ) {}

                public function parameter($key = null, $default = null)
                {
                    if ($key === 'book') {
                        return $this->book;
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

    private function createBook(
        string $isbn = '1234567890123',
        string $title = 'テスト書籍'
    ): Book {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => uniqid('test-', true).'@example.com',
            'password' => 'password',
        ]);

        return Book::create([
            'user_id' => $user->id,
            'title' => $title,
            'author' => 'テスト著者',
            'isbn' => $isbn,
            'published_date' => '2026-01-01',
            'description' => 'テスト用の書籍です。',
            'image_url' => null,
        ]);
    }

    private function createGenre(): Genre
    {
        return Genre::create([
            'name' => 'テストジャンル',
        ]);
    }

    private function validBookData(string $isbn = '1234567890123'): array
    {
        $genre = $this->createGenre();

        return [
            'title' => '更新後の書籍',
            'author' => '更新後の著者',
            'isbn' => $isbn,
            'published_date' => '2026-02-01',
            'description' => '更新後の説明です。',
            'image_url' => 'https://example.com/image.jpg',
            'genres' => [$genre->id],
        ];
    }

    public function test_valid_book_data_passes_validation(): void
    {
        $book = $this->createBook();

        $this->assertTrue(
            $this->validate(
                $book,
                $this->validBookData($book->isbn)
            )
        );
    }

    public function test_title_is_required(): void
    {
        $book = $this->createBook();
        $data = $this->validBookData($book->isbn);
        $data['title'] = '';

        $this->assertFalse($this->validate($book, $data));
    }

    public function test_title_at_255_characters_is_valid(): void
    {
        $book = $this->createBook();
        $data = $this->validBookData($book->isbn);
        $data['title'] = str_repeat('あ', 255);

        $this->assertTrue($this->validate($book, $data));
    }

    public function test_title_over_255_characters_is_invalid(): void
    {
        $book = $this->createBook();
        $data = $this->validBookData($book->isbn);
        $data['title'] = str_repeat('あ', 256);

        $this->assertFalse($this->validate($book, $data));
    }

    public function test_author_is_required(): void
    {
        $book = $this->createBook();
        $data = $this->validBookData($book->isbn);
        $data['author'] = '';

        $this->assertFalse($this->validate($book, $data));
    }

    public function test_author_at_255_characters_is_valid(): void
    {
        $book = $this->createBook();
        $data = $this->validBookData($book->isbn);
        $data['author'] = str_repeat('あ', 255);

        $this->assertTrue($this->validate($book, $data));
    }

    public function test_author_over_255_characters_is_invalid(): void
    {
        $book = $this->createBook();
        $data = $this->validBookData($book->isbn);
        $data['author'] = str_repeat('あ', 256);

        $this->assertFalse($this->validate($book, $data));
    }

    public function test_isbn_is_required(): void
    {
        $book = $this->createBook();
        $data = $this->validBookData($book->isbn);
        $data['isbn'] = '';

        $this->assertFalse($this->validate($book, $data));
    }

    public function test_isbn_with_13_digits_is_valid(): void
    {
        $book = $this->createBook();
        $data = $this->validBookData($book->isbn);
        $data['isbn'] = '9876543210123';

        $this->assertTrue($this->validate($book, $data));
    }

    public function test_isbn_with_12_digits_is_invalid(): void
    {
        $book = $this->createBook();
        $data = $this->validBookData($book->isbn);
        $data['isbn'] = '123456789012';

        $this->assertFalse($this->validate($book, $data));
    }

    public function test_isbn_with_14_digits_is_invalid(): void
    {
        $book = $this->createBook();
        $data = $this->validBookData($book->isbn);
        $data['isbn'] = '12345678901234';

        $this->assertFalse($this->validate($book, $data));
    }

    public function test_own_isbn_is_allowed(): void
    {
        $book = $this->createBook('1234567890123');
        $data = $this->validBookData($book->isbn);

        $this->assertTrue($this->validate($book, $data));
    }

    public function test_isbn_used_by_another_book_is_invalid(): void
    {
        $book = $this->createBook('1234567890123');
        $this->createBook('9876543210123', '別の書籍');

        $data = $this->validBookData($book->isbn);
        $data['isbn'] = '9876543210123';

        $this->assertFalse($this->validate($book, $data));
    }

    public function test_published_date_is_required(): void
    {
        $book = $this->createBook();
        $data = $this->validBookData($book->isbn);
        $data['published_date'] = '';

        $this->assertFalse($this->validate($book, $data));
    }

    public function test_invalid_published_date_is_invalid(): void
    {
        $book = $this->createBook();
        $data = $this->validBookData($book->isbn);
        $data['published_date'] = 'invalid-date';

        $this->assertFalse($this->validate($book, $data));
    }

    public function test_description_at_1000_characters_is_valid(): void
    {
        $book = $this->createBook();
        $data = $this->validBookData($book->isbn);
        $data['description'] = str_repeat('あ', 1000);

        $this->assertTrue($this->validate($book, $data));
    }

    public function test_description_over_1000_characters_is_invalid(): void
    {
        $book = $this->createBook();
        $data = $this->validBookData($book->isbn);
        $data['description'] = str_repeat('あ', 1001);

        $this->assertFalse($this->validate($book, $data));
    }

    public function test_description_can_be_null(): void
    {
        $book = $this->createBook();
        $data = $this->validBookData($book->isbn);
        $data['description'] = null;

        $this->assertTrue($this->validate($book, $data));
    }

    public function test_image_url_is_valid(): void
    {
        $book = $this->createBook();
        $data = $this->validBookData($book->isbn);

        $this->assertTrue($this->validate($book, $data));
    }

    public function test_invalid_image_url_is_invalid(): void
    {
        $book = $this->createBook();
        $data = $this->validBookData($book->isbn);
        $data['image_url'] = 'invalid-url';

        $this->assertFalse($this->validate($book, $data));
    }

    public function test_image_url_can_be_null(): void
    {
        $book = $this->createBook();
        $data = $this->validBookData($book->isbn);
        $data['image_url'] = null;

        $this->assertTrue($this->validate($book, $data));
    }

    public function test_genres_are_required(): void
    {
        $book = $this->createBook();
        $data = $this->validBookData($book->isbn);
        $data['genres'] = [];

        $this->assertFalse($this->validate($book, $data));
    }

    public function test_genres_must_be_array(): void
    {
        $book = $this->createBook();
        $data = $this->validBookData($book->isbn);
        $data['genres'] = '1';

        $this->assertFalse($this->validate($book, $data));
    }

    public function test_genres_must_contain_existing_genre_ids(): void
    {
        $book = $this->createBook();
        $data = $this->validBookData($book->isbn);
        $data['genres'] = [99999];

        $this->assertFalse($this->validate($book, $data));
    }
}
