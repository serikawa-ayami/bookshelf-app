<?php

namespace Tests\Feature\Api\V1;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookIndexApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * No.57
     * 書籍一覧APIが200 OKを返し、書籍情報・ジャンル情報・
     * average_rating・review_countを取得でき、reviewsを含まないこと。
     */
    public function test_index_returns_books_without_reviews(): void
    {
        $user = User::factory()->create();

        $genre = Genre::create([
            'name' => '小説',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000001',
            'published_date' => '2026-01-01',
            'description' => 'テスト説明',
            'image_url' => null,
        ]);

        $book->genres()->attach($genre->id);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => 'テストレビュー',
        ]);

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
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
                ],
                'links',
                'meta',
            ])
            ->assertJsonPath('data.0.title', 'テスト書籍')
            ->assertJsonPath('data.0.average_rating', '4.0000')
            ->assertJsonPath('data.0.review_count', 1)
            ->assertJsonMissingPath('data.0.reviews');
    }

    /**
     * No.57
     * keywordにタイトルを指定して書籍を検索できること。
     */
    public function test_index_can_search_by_title(): void
    {
        $this->createBook(['title' => 'Laravel入門']);

        $this->createBook(['title' => 'PHP入門']);

        $response = $this->getJson('/api/v1/books?keyword=Laravel');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Laravel入門');
    }

    /**
     * No.57
     * keywordに著者名を指定して書籍を検索できること。
     */
    public function test_index_can_search_by_author(): void
    {
        $this->createBook(['author' => '山田太郎']);

        $this->createBook(['author' => '佐藤花子']);

        $response = $this->getJson('/api/v1/books?keyword=山田');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.author', '山田太郎');
    }

    /**
     * No.57
     * keywordにISBNを指定して書籍を検索できること。
     */
    public function test_index_can_search_by_isbn(): void
    {
        $this->createBook(['isbn' => '9784000000001']);

        $this->createBook(['isbn' => '9784000000002']);

        $response = $this->getJson('/api/v1/books?keyword=9784000000001');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.isbn', '9784000000001');
    }

    /**
     * No.57
     * genre_idを指定して該当ジャンルの書籍に絞り込めること。
     */
    public function test_index_can_filter_by_genre_id(): void
    {
        $genre1 = Genre::create(['name' => '小説']);
        $genre2 = Genre::create(['name' => 'ビジネス']);

        $book1 = $this->createBook();
        $book2 = $this->createBook();

        $book1->genres()->attach($genre1->id);
        $book2->genres()->attach($genre2->id);

        $response = $this->getJson('/api/v1/books?genre_id=' . $genre1->id);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $book1->id);
    }

    /**
     * No.57
     * keywordとgenre_idを同時指定した場合にAND条件で絞り込めること。
     */
    public function test_index_can_filter_by_keyword_and_genre_id(): void
    {
        $genre1 = Genre::create(['name' => '小説']);
        $genre2 = Genre::create(['name' => 'ビジネス']);

        $book1 = $this->createBook(['title' => 'Laravel入門']);
        $book2 = $this->createBook(['title' => 'Laravel実践']);

        $book1->genres()->attach($genre1->id);
        $book2->genres()->attach($genre2->id);

        $response = $this->getJson(
            '/api/v1/books?keyword=Laravel&genre_id=' . $genre1->id
        );

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $book1->id);
    }

    /**
     * No.57
     * pageとper_pageを指定してページネーションが機能すること。
     */
    public function test_index_supports_pagination(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            $this->createBook();
        }

        $response = $this->getJson('/api/v1/books?page=2&per_page=5');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonPath('meta.total', 15);
    }

    /**
     * No.57
     * デフォルト値がpage=1・per_page=10であること。
     */
    public function test_index_uses_default_pagination_values(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            $this->createBook();
        }

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 15);
    }

    /**
     * No.57
     * per_pageに100を指定して最大件数を取得できること。
     */
    public function test_index_supports_maximum_per_page_of_100(): void
    {
        for ($i = 1; $i <= 105; $i++) {
            $this->createBook();
        }

        $response = $this->getJson('/api/v1/books?per_page=100');

        $response->assertStatus(200)
            ->assertJsonCount(100, 'data')
            ->assertJsonPath('meta.per_page', 100)
            ->assertJsonPath('meta.total', 105);
    }

    /**
     * No.57
     * ページネーション情報としてlinksとmetaが返ること。
     */
    public function test_index_returns_links_and_meta(): void
    {
        $this->createBook();

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'links',
                    'path',
                    'per_page',
                    'to',
                    'total',
                ],
            ]);
    }

    /**
     * No.57のテスト用書籍を作成する。
     */
    private function createBook(array $attributes = []): Book
    {
        $user = User::factory()->create();

        return Book::create(array_merge([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => fake()->unique()->numerify('#############'),
            'published_date' => '2026-01-01',
            'description' => 'テスト説明',
            'image_url' => null,
        ], $attributes));
    }

    /**
     * No.58
     * 不正なkeyword・genre_id・page・per_pageを指定した場合に、
     * 422と日本語のバリデーションエラー形式が返ること。
     */
    public function test_index_returns_validation_errors_for_invalid_parameters(): void
    {
        $invalidParameters = [
            'keyword' => ['keyword' => ['不正な値']],
            'genre_id' => ['genre_id' => 'invalid'],
            'page' => ['page' => 0],
            'per_page' => ['per_page' => 101],
        ];

        foreach ($invalidParameters as $key => $parameter) {
            $response = $this->getJson(
                '/api/v1/books?' . http_build_query($parameter)
            );

            $response->assertStatus(422)
                ->assertJsonStructure([
                    'message',
                    'errors' => [$key],
                ]);

            $errorMessage = json_encode(
                $response->json(),
                JSON_UNESCAPED_UNICODE
            );

            $this->assertMatchesRegularExpression(
                '/[ぁ-んァ-ヶ一-龯]/u',
                $errorMessage
            );
        }
    }
}