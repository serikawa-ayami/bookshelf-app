<?php

namespace Tests\Unit\Requests\Api\V1;

use App\Http\Requests\Api\V1\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateBookRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * No.56：正常な入力値でバリデーションを通過すること。
     */
    public function test_valid_data_passes_validation(): void
    {
        $book = $this->createBook();
        $data = $this->validData();

        $validator = $this->makeValidator($data, $book);

        $this->assertTrue($validator->passes());
    }

    /**
     * No.56：必須項目が未入力の場合にバリデーションエラーになること。
     */
    public function test_required_fields_are_invalid_when_missing(): void
    {
        $book = $this->createBook();

        $validator = $this->makeValidator([], $book);

        foreach ([
            'title',
            'author',
            'isbn',
            'published_date',
            'genres',
        ] as $field) {
            $this->assertTrue($validator->errors()->has($field));
        }
    }

    /**
     * No.56：タイトルが255文字を超える場合にエラーになること。
     */
    public function test_title_over_255_characters_is_invalid(): void
    {
        $book = $this->createBook();
        $data = $this->validData();
        $data['title'] = str_repeat('あ', 256);

        $this->assertInvalid($data, $book, 'title');
    }

    /**
     * No.56：著者名が255文字を超える場合にエラーになること。
     */
    public function test_author_over_255_characters_is_invalid(): void
    {
        $book = $this->createBook();
        $data = $this->validData();
        $data['author'] = str_repeat('あ', 256);

        $this->assertInvalid($data, $book, 'author');
    }

    /**
     * No.56：ISBNが13桁でない場合にエラーになること。
     */
    public function test_isbn_not_13_digits_is_invalid(): void
    {
        $book = $this->createBook();
        $data = $this->validData();
        $data['isbn'] = '123456789012';

        $this->assertInvalid($data, $book, 'isbn');
    }

    /**
     * No.56：ISBNに数字以外が含まれる場合にエラーになること。
     */
    public function test_isbn_with_non_digits_is_invalid(): void
    {
        $book = $this->createBook();
        $data = $this->validData();
        $data['isbn'] = '123456789012A';

        $this->assertInvalid($data, $book, 'isbn');
    }

    /**
     * No.56：更新対象の書籍自身のISBNを指定してもエラーにならないこと。
     */
    public function test_same_isbn_as_current_book_is_valid(): void
    {
        $book = $this->createBook();
        $data = $this->validData();
        $data['isbn'] = $book->isbn;

        $validator = $this->makeValidator($data, $book);

        $this->assertTrue($validator->passes());
    }

    /**
     * No.56：別の書籍に登録済みのISBNを指定した場合にエラーになること。
     */
    public function test_duplicate_isbn_of_another_book_is_invalid(): void
    {
        $book = $this->createBook();
        $anotherBook = $this->createBook([
            'isbn' => '9781234567891',
        ]);

        $data = $this->validData();
        $data['isbn'] = $anotherBook->isbn;

        $this->assertInvalid($data, $book, 'isbn');
    }

    /**
     * No.56：出版日が正しい日付でない場合にエラーになること。
     */
    public function test_invalid_published_date_is_invalid(): void
    {
        $book = $this->createBook();
        $data = $this->validData();
        $data['published_date'] = 'invalid-date';

        $this->assertInvalid($data, $book, 'published_date');
    }

    /**
     * No.56：説明が1000文字を超える場合にエラーになること。
     */
    public function test_description_over_1000_characters_is_invalid(): void
    {
        $book = $this->createBook();
        $data = $this->validData();
        $data['description'] = str_repeat('あ', 1001);

        $this->assertInvalid($data, $book, 'description');
    }

    /**
     * No.56：画像URLの形式が正しくない場合にエラーになること。
     */
    public function test_invalid_image_url_is_invalid(): void
    {
        $book = $this->createBook();
        $data = $this->validData();
        $data['image_url'] = 'invalid-url';

        $this->assertInvalid($data, $book, 'image_url');
    }

    /**
     * No.56：ジャンルが空配列の場合にエラーになること。
     */
    public function test_empty_genres_is_invalid(): void
    {
        $book = $this->createBook();
        $data = $this->validData();
        $data['genres'] = [];

        $this->assertInvalid($data, $book, 'genres');
    }

    /**
     * No.56：存在しないジャンルIDを指定した場合にエラーになること。
     */
    public function test_non_existing_genre_id_is_invalid(): void
    {
        $book = $this->createBook();
        $data = $this->validData();
        $data['genres'] = [999999];

        $this->assertInvalid($data, $book, 'genres.0');
    }

    /**
     * テスト用の書籍を作成する。
     */
    private function createBook(array $attributes = []): Book
    {
        $user = User::factory()->create();

        return Book::create(array_merge([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9781234567890',
            'published_date' => '2026-01-01',
        ], $attributes));
    }

    /**
     * テスト用の正常な入力データを作成する。
     */
    private function validData(): array
    {
        $genre = Genre::create([
            'name' => '小説'.uniqid(),
        ]);

        return [
            'title' => '更新後のテスト書籍',
            'author' => '更新後のテスト著者',
            'isbn' => '9781234567892',
            'published_date' => '2026-02-01',
            'description' => 'テスト説明',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [$genre->id],
        ];
    }

    /**
     * 更新対象の書籍を設定してバリデータを作成する。
     */
    private function makeValidator(array $data, Book $book)
    {
        $request = new UpdateBookRequest;

        $request->setRouteResolver(function () use ($book) {
            return new class($book)
            {
                public function __construct(
                    private Book $book
                ) {}

                public function parameter($key)
                {
                    return $key === 'book' ? $this->book : null;
                }
            };
        });

        return Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        );
    }

    /**
     * 指定した項目がバリデーションエラーになることを確認する。
     */
    private function assertInvalid(
        array $data,
        Book $book,
        string $field
    ): void {
        $validator = $this->makeValidator($data, $book);

        $this->assertTrue($validator->errors()->has($field));
    }
}
