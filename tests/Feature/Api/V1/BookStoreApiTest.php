<?php

namespace Tests\Feature\Api\V1;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookStoreApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * No.61
     * 有効な入力を送信した場合に201 Createdが返り、
     * 書籍とジャンル関連が保存され、登録結果に必要な情報が含まれること。
     */
    public function test_store_creates_book_and_returns_book_list_resource(): void
    {
        $user = User::factory()->create();

        $genre1 = Genre::create(['name' => '小説']);
        $genre2 = Genre::create(['name' => 'ビジネス']);

        $payload = [
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000001',
            'published_date' => '2026-01-01',
            'description' => 'テスト説明',
            'image_url' => null,
            'genres' => [$genre1->id, $genre2->id],
        ];

        $response = $this->postJson('/api/v1/books', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'author',
                    'isbn',
                    'published_date',
                    'description',
                    'image_url',
                    'genres' => [
                        '*' => ['id', 'name'],
                    ],
                    'average_rating',
                    'review_count',
                ],
            ])
            ->assertJsonPath('data.title', 'テスト書籍')
            ->assertJsonPath('data.author', 'テスト著者')
            ->assertJsonPath('data.isbn', '9784000000001')
            ->assertJsonPath('data.average_rating', null)
            ->assertJsonPath('data.review_count', 0);

        $bookId = $response->json('data.id');

        $this->assertDatabaseHas('books', [
            'id' => $bookId,
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'isbn' => '9784000000001',
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $bookId,
            'genre_id' => $genre1->id,
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $bookId,
            'genre_id' => $genre2->id,
        ]);
    }

    /**
     * No.62
     * 不正な入力を送信した場合に422と
     * 項目ごとのバリデーションエラーメッセージが返り、
     * 書籍が登録されないこと。
     */
    public function test_store_returns_validation_errors_for_invalid_input(): void
    {
        $user = User::factory()->create();

        $payload = [
            'user_id' => $user->id,
            'title' => '',
            'author' => '',
            'isbn' => 'invalid',
            'published_date' => 'invalid',
            'description' => str_repeat('あ', 1001),
            'image_url' => 'invalid-url',
            'genres' => [],
        ];

        $response = $this->postJson('/api/v1/books', $payload);

        $response->assertStatus(422)
            ->assertJsonPath(
                'message',
                '入力内容に誤りがあります。'
            )
            ->assertJsonPath(
                'errors.title.0',
                'タイトルは必須です。'
            )
            ->assertJsonPath(
                'errors.author.0',
                '著者は必須です。'
            )
            ->assertJsonPath(
                'errors.isbn.0',
                'ISBNは13桁の数字で入力してください。'
            )
            ->assertJsonPath(
                'errors.published_date.0',
                '出版日は正しい日付を入力してください。'
            )
            ->assertJsonPath(
                'errors.description.0',
                '説明は1000文字以内で入力してください。'
            )
            ->assertJsonPath(
                'errors.image_url.0',
                '画像URLの形式が正しくありません。'
            )
            ->assertJsonPath(
                'errors.genres.0',
                'ジャンルは必須です。'
            );

        $this->assertDatabaseCount('books', 0);
    }
}
