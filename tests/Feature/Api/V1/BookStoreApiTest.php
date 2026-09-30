<?php

namespace Tests\Feature\Api\V1;

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
        $existingUser = User::factory()->create();
        $genre = Genre::create(['name' => '小説']);
        $existingIsbn = '9784000000001';

        $this->postJson('/api/v1/books', [
            'user_id' => $existingUser->id,
            'title' => '既存書籍',
            'author' => '既存著者',
            'isbn' => $existingIsbn,
            'published_date' => '2026-01-01',
            'description' => null,
            'image_url' => null,
            'genres' => [$genre->id],
        ])->assertStatus(201);

        $invalidInputs = [
            [
                'payload' => [
                    'title' => 'テスト書籍',
                    'author' => 'テスト著者',
                    'isbn' => '9784000000002',
                    'published_date' => '2026-01-01',
                    'description' => null,
                    'image_url' => null,
                    'genres' => [$genre->id],
                ],
                'key' => 'user_id',
                'message' => '登録者IDは必須です。',
            ],
            [
                'payload' => [
                    'user_id' => 'invalid',
                    'title' => 'テスト書籍',
                    'author' => 'テスト著者',
                    'isbn' => '9784000000002',
                    'published_date' => '2026-01-01',
                    'description' => null,
                    'image_url' => null,
                    'genres' => [$genre->id],
                ],
                'key' => 'user_id',
                'message' => '登録者IDは整数で指定してください。',
            ],
            [
                'payload' => [
                    'user_id' => 999999,
                    'title' => 'テスト書籍',
                    'author' => 'テスト著者',
                    'isbn' => '9784000000002',
                    'published_date' => '2026-01-01',
                    'description' => null,
                    'image_url' => null,
                    'genres' => [$genre->id],
                ],
                'key' => 'user_id',
                'message' => '指定した登録者が存在しません。',
            ],
            [
                'payload' => [
                    'user_id' => $user->id,
                    'author' => 'テスト著者',
                    'isbn' => '9784000000002',
                    'published_date' => '2026-01-01',
                    'description' => null,
                    'image_url' => null,
                    'genres' => [$genre->id],
                ],
                'key' => 'title',
                'message' => 'タイトルは必須です。',
            ],
            [
                'payload' => [
                    'user_id' => $user->id,
                    'title' => 'テスト書籍',
                    'isbn' => '9784000000002',
                    'published_date' => '2026-01-01',
                    'description' => null,
                    'image_url' => null,
                    'genres' => [$genre->id],
                ],
                'key' => 'author',
                'message' => '著者は必須です。',
            ],
            [
                'payload' => [
                    'user_id' => $user->id,
                    'title' => ['不正な値'],
                    'author' => 'テスト著者',
                    'isbn' => '9784000000002',
                    'published_date' => '2026-01-01',
                    'description' => null,
                    'image_url' => null,
                    'genres' => [$genre->id],
                ],
                'key' => 'title',
                'message' => 'タイトルは文字列で入力してください。',
            ],
            [
                'payload' => [
                    'user_id' => $user->id,
                    'title' => str_repeat('あ', 256),
                    'author' => 'テスト著者',
                    'isbn' => '9784000000002',
                    'published_date' => '2026-01-01',
                    'description' => null,
                    'image_url' => null,
                    'genres' => [$genre->id],
                ],
                'key' => 'title',
                'message' => 'タイトルは255文字以内で入力してください。',
            ],
            [
                'payload' => [
                    'user_id' => $user->id,
                    'title' => 'テスト書籍',
                    'isbn' => '9784000000002',
                    'published_date' => '2026-01-01',
                    'description' => null,
                    'image_url' => null,
                    'genres' => [$genre->id],
                ],
                'key' => 'author',
                'message' => '著者は必須です。',
            ],
            [
                'payload' => [
                    'user_id' => $user->id,
                    'title' => 'テスト書籍',
                    'author' => ['不正な値'],
                    'isbn' => '9784000000002',
                    'published_date' => '2026-01-01',
                    'description' => null,
                    'image_url' => null,
                    'genres' => [$genre->id],
                ],
                'key' => 'author',
                'message' => '著者は文字列で入力してください。',
            ],
            [
                'payload' => [
                    'user_id' => $user->id,
                    'title' => 'テスト書籍',
                    'author' => str_repeat('あ', 256),
                    'isbn' => '9784000000002',
                    'published_date' => '2026-01-01',
                    'description' => null,
                    'image_url' => null,
                    'genres' => [$genre->id],
                ],
                'key' => 'author',
                'message' => '著者は255文字以内で入力してください。',
            ],
            [
                'payload' => [
                    'user_id' => $user->id,
                    'title' => 'テスト書籍',
                    'author' => 'テスト著者',
                    'published_date' => '2026-01-01',
                    'description' => null,
                    'image_url' => null,
                    'genres' => [$genre->id],
                ],
                'key' => 'isbn',
                'message' => 'ISBNは必須です。',
            ],
            [
                'payload' => [
                    'user_id' => $user->id,
                    'title' => 'テスト書籍',
                    'author' => 'テスト著者',
                    'isbn' => '123456789012',
                    'published_date' => '2026-01-01',
                    'description' => null,
                    'image_url' => null,
                    'genres' => [$genre->id],
                ],
                'key' => 'isbn',
                'message' => 'ISBNは13桁の数字で入力してください。',
            ],
            [
                'payload' => [
                    'user_id' => $user->id,
                    'title' => 'テスト書籍',
                    'author' => 'テスト著者',
                    'isbn' => $existingIsbn,
                    'published_date' => '2026-01-01',
                    'description' => null,
                    'image_url' => null,
                    'genres' => [$genre->id],
                ],
                'key' => 'isbn',
                'message' => 'このISBNはすでに登録されています。',
            ],
            [
                'payload' => [
                    'user_id' => $user->id,
                    'title' => 'テスト書籍',
                    'author' => 'テスト著者',
                    'isbn' => '9784000000002',
                    'description' => null,
                    'image_url' => null,
                    'genres' => [$genre->id],
                ],
                'key' => 'published_date',
                'message' => '出版日は必須です。',
            ],
            [
                'payload' => [
                    'user_id' => $user->id,
                    'title' => 'テスト書籍',
                    'author' => 'テスト著者',
                    'isbn' => '9784000000002',
                    'published_date' => 'invalid-date',
                    'description' => null,
                    'image_url' => null,
                    'genres' => [$genre->id],
                ],
                'key' => 'published_date',
                'message' => '出版日は正しい日付を入力してください。',
            ],
            [
                'payload' => [
                    'user_id' => $user->id,
                    'title' => 'テスト書籍',
                    'author' => 'テスト著者',
                    'isbn' => '9784000000002',
                    'published_date' => '2026-01-01',
                    'description' => ['不正な値'],
                    'image_url' => null,
                    'genres' => [$genre->id],
                ],
                'key' => 'description',
                'message' => '説明は文字列で入力してください。',
            ],
            [
                'payload' => [
                    'user_id' => $user->id,
                    'title' => 'テスト書籍',
                    'author' => 'テスト著者',
                    'isbn' => '9784000000002',
                    'published_date' => '2026-01-01',
                    'description' => str_repeat('あ', 1001),
                    'image_url' => null,
                    'genres' => [$genre->id],
                ],
                'key' => 'description',
                'message' => '説明は1000文字以内で入力してください。',
            ],
            [
                'payload' => [
                    'user_id' => $user->id,
                    'title' => 'テスト書籍',
                    'author' => 'テスト著者',
                    'isbn' => '9784000000002',
                    'published_date' => '2026-01-01',
                    'description' => null,
                    'image_url' => 'https://example.com/'.str_repeat('a', 240),
                    'genres' => [$genre->id],
                ],
                'key' => 'image_url',
                'message' => '画像URLは255文字以内で入力してください。',
            ],
            [
                'payload' => [
                    'user_id' => $user->id,
                    'title' => 'テスト書籍',
                    'author' => 'テスト著者',
                    'isbn' => '9784000000002',
                    'published_date' => '2026-01-01',
                    'description' => null,
                    'image_url' => 'invalid-url',
                    'genres' => [$genre->id],
                ],
                'key' => 'image_url',
                'message' => '画像URLの形式が正しくありません。',
            ],
            [
                'payload' => [
                    'user_id' => $user->id,
                    'title' => 'テスト書籍',
                    'author' => 'テスト著者',
                    'isbn' => '9784000000002',
                    'published_date' => '2026-01-01',
                    'description' => null,
                    'image_url' => null,
                ],
                'key' => 'genres',
                'message' => 'ジャンルは必須です。',
            ],
            [
                'payload' => [
                    'user_id' => $user->id,
                    'title' => 'テスト書籍',
                    'author' => 'テスト著者',
                    'isbn' => '9784000000002',
                    'published_date' => '2026-01-01',
                    'description' => null,
                    'image_url' => null,
                    'genres' => 'invalid',
                ],
                'key' => 'genres',
                'message' => 'ジャンルの形式が正しくありません。',
            ],
            [
                'payload' => [
                    'user_id' => $user->id,
                    'title' => 'テスト書籍',
                    'author' => 'テスト著者',
                    'isbn' => '9784000000002',
                    'published_date' => '2026-01-01',
                    'description' => null,
                    'image_url' => null,
                    'genres' => [999999],
                ],
                'key' => 'genres.0',
                'message' => '選択したジャンルが存在しません。',
            ],
        ];

        foreach ($invalidInputs as $invalidInput) {
            $response = $this->postJson(
                '/api/v1/books',
                $invalidInput['payload']
            );

            $response->assertStatus(422)
                ->assertJsonPath(
                    'message',
                    '入力内容に誤りがあります。'
                );

            if ($invalidInput['key'] === 'genres.0') {
                $response->assertJsonPath(
                    'errors',
                    [
                        'genres.0' => [$invalidInput['message']],
                    ]
                );
            } else {
                $response->assertJsonStructure([
                    'errors' => [$invalidInput['key']],
                ])
                    ->assertJsonPath(
                        'errors.'.$invalidInput['key'].'.0',
                        $invalidInput['message']
                    );
            }
        }

        $this->assertDatabaseCount('books', 1);
    }
}
