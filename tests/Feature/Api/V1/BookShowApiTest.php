<?php

namespace Tests\Feature\Api\V1;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookShowApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * No.59
     * 書籍詳細APIが200 OKを返し、書籍基本情報・genres・reviewsを取得でき、
     * レビュー情報がISO 8601形式で返り、average_ratingとreview_countを含まないこと。
     */
    public function test_show_returns_book_details_with_reviews(): void
    {
        $user = User::factory()->create([
            'name' => 'テストユーザー',
        ]);

        $genre = Genre::create([
            'name' => '小説',
        ]);

        $book = $this->createBook();

        $book->genres()->attach($genre->id);

        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '面白い書籍でした。',
        ]);

        $response = $this->getJson('/api/v1/books/'.$book->id);

        $response->assertStatus(200)
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
                    'reviews' => [
                        '*' => [
                            'id',
                            'user_name',
                            'rating',
                            'comment',
                            'created_at',
                        ],
                    ],
                ],
            ])
            ->assertJsonPath('data.id', $book->id)
            ->assertJsonPath('data.genres.0.name', '小説')
            ->assertJsonPath('data.reviews.0.id', $review->id)
            ->assertJsonPath('data.reviews.0.user_name', 'テストユーザー')
            ->assertJsonPath('data.reviews.0.rating', 4)
            ->assertJsonPath('data.reviews.0.comment', '面白い書籍でした。')
            ->assertJsonMissingPath('data.average_rating')
            ->assertJsonMissingPath('data.review_count');

        $createdAt = $response->json('data.reviews.0.created_at');

        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})$/',
            $createdAt
        );
    }

    /**
     * No.60
     * 存在しない書籍IDを指定した場合に404 Not Foundが返り、
     * エラーメッセージが表示されること。
     */
    public function test_show_returns_404_for_non_existing_book(): void
    {
        $response = $this->getJson('/api/v1/books/999999');

        $response->assertStatus(404)
            ->assertJsonFragment([
                'error' => '書籍が見つかりませんでした。',
            ]);
    }

    /**
     * テスト用書籍を作成する。
     */
    private function createBook(): Book
    {
        $user = User::factory()->create();

        return Book::create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => fake()->unique()->numerify('#############'),
            'published_date' => '2026-01-01',
            'description' => 'テスト説明',
            'image_url' => null,
        ]);
    }
}
