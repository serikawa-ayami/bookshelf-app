<?php

namespace Tests\Unit\Requests\Api\V1;

use App\Http\Requests\Api\V1\StoreBookRequest;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreBookRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * No.55：正常な入力値でバリデーションを通過すること。
     */
    public function test_valid_data_passes_validation(): void
    {
        $user = User::factory()->create();
        $genre = Genre::create(['name' => '小説']);

        $data = [
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9781234567890',
            'published_date' => '2026-01-01',
            'description' => 'テスト説明',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [$genre->id],
        ];

        $validator = Validator::make(
            $data,
            (new StoreBookRequest)->rules(),
            (new StoreBookRequest)->messages()
        );

        $this->assertTrue($validator->passes());
    }

    /**
     * No.55：必須項目が未入力の場合にバリデーションエラーになること。
     */
    public function test_required_fields_are_invalid_when_missing(): void
    {
        $validator = Validator::make(
            [],
            (new StoreBookRequest)->rules(),
            (new StoreBookRequest)->messages()
        );

        foreach ([
            'user_id',
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
     * No.55：存在しない登録者IDを指定した場合にエラーになること。
     */
    public function test_non_existing_user_id_is_invalid(): void
    {
        $data = $this->validData();
        $data['user_id'] = 999999;

        $this->assertInvalid($data, 'user_id');
    }

    /**
     * No.55：タイトルが255文字を超える場合にエラーになること。
     */
    public function test_title_over_255_characters_is_invalid(): void
    {
        $data = $this->validData();
        $data['title'] = str_repeat('あ', 256);

        $this->assertInvalid($data, 'title');
    }

    /**
     * No.55：著者名が255文字を超える場合にエラーになること。
     */
    public function test_author_over_255_characters_is_invalid(): void
    {
        $data = $this->validData();
        $data['author'] = str_repeat('あ', 256);

        $this->assertInvalid($data, 'author');
    }

    /**
     * No.55：ISBNが13桁でない場合にエラーになること。
     */
    public function test_isbn_not_13_digits_is_invalid(): void
    {
        $data = $this->validData();
        $data['isbn'] = '123456789012';

        $this->assertInvalid($data, 'isbn');
    }

    /**
     * No.55：ISBNに数字以外が含まれる場合にエラーになること。
     */
    public function test_isbn_with_non_digits_is_invalid(): void
    {
        $data = $this->validData();
        $data['isbn'] = '123456789012A';

        $this->assertInvalid($data, 'isbn');
    }

    /**
     * No.55：登録済みのISBNを指定した場合にエラーになること。
     */
    public function test_duplicate_isbn_is_invalid(): void
    {
        $user = User::factory()->create();

        Book::create([
            'user_id' => $user->id,
            'title' => '既存書籍',
            'author' => '既存著者',
            'isbn' => '9781234567890',
            'published_date' => '2026-01-01',
        ]);

        $data = $this->validData();
        $data['isbn'] = '9781234567890';

        $this->assertInvalid($data, 'isbn');
    }

    /**
     * No.55：出版日が正しい日付でない場合にエラーになること。
     */
    public function test_invalid_published_date_is_invalid(): void
    {
        $data = $this->validData();
        $data['published_date'] = 'invalid-date';

        $this->assertInvalid($data, 'published_date');
    }

    /**
     * No.55：説明が1000文字を超える場合にエラーになること。
     */
    public function test_description_over_1000_characters_is_invalid(): void
    {
        $data = $this->validData();
        $data['description'] = str_repeat('あ', 1001);

        $this->assertInvalid($data, 'description');
    }

    /**
     * No.55：画像URLの形式が正しくない場合にエラーになること。
     */
    public function test_invalid_image_url_is_invalid(): void
    {
        $data = $this->validData();
        $data['image_url'] = 'invalid-url';

        $this->assertInvalid($data, 'image_url');
    }

    /**
     * No.55：ジャンルが空配列の場合にエラーになること。
     */
    public function test_empty_genres_is_invalid(): void
    {
        $data = $this->validData();
        $data['genres'] = [];

        $this->assertInvalid($data, 'genres');
    }

    /**
     * No.55：存在しないジャンルIDを指定した場合にエラーになること。
     */
    public function test_non_existing_genre_id_is_invalid(): void
    {
        $data = $this->validData();
        $data['genres'] = [999999];

        $this->assertInvalid($data, 'genres.0');
    }

    /**
     * テスト用の正常な入力データを作成する。
     */
    private function validData(): array
    {
        $user = User::factory()->create();
        $genre = Genre::create(['name' => '小説']);

        return [
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9781234567890',
            'published_date' => '2026-01-01',
            'description' => 'テスト説明',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [$genre->id],
        ];
    }

    /**
     * 指定した項目がバリデーションエラーになることを確認する。
     */
    private function assertInvalid(array $data, string $field): void
    {
        $validator = Validator::make(
            $data,
            (new StoreBookRequest)->rules(),
            (new StoreBookRequest)->messages()
        );

        $this->assertTrue($validator->errors()->has($field));
    }
}
