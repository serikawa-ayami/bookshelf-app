<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private int $isbnCounter = 1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'テストユーザー',
            'email' => 'ranking-test@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    /**
     * テスト用の書籍を作成する。
     */
    private function createBook(string $title): Book
    {
        $isbn = str_pad(
            (string) $this->isbnCounter++,
            13,
            '0',
            STR_PAD_LEFT
        );

        return Book::create([
            'user_id' => $this->user->id,
            'title' => $title,
            'author' => 'テスト著者',
            'isbn' => $isbn,
            'published_date' => '2026-01-01',
        ]);
    }

    /**
     * テスト用の書籍とレビューを作成する。
     *
     * @param  array<int, int>  $ratings
     */
    private function createBookWithReviews(
        string $title,
        array $ratings
    ): Book {
        $book = $this->createBook($title);

        foreach ($ratings as $rating) {
            Review::create([
                'user_id' => $this->user->id,
                'book_id' => $book->id,
                'rating' => $rating,
                'comment' => 'テストレビュー',
            ]);
        }

        return $book;
    }

    /**
     * No.50
     *
     * レビューがある書籍とレビューがない書籍を用意して
     * /ranking を表示し、レビューのない書籍が
     * ランキングから除外されること。
     */
    public function test_books_without_reviews_are_excluded_from_ranking(): void
    {
        $reviewedBook = $this->createBookWithReviews(
            'レビューあり書籍',
            [5, 4]
        );

        $unreviewedBook = $this->createBook(
            'レビューなし書籍'
        );

        $response = $this->get(route('ranking.index'));

        $response->assertOk();

        $response->assertSee('レビューあり書籍');
        $response->assertDontSee('レビューなし書籍');

        $response->assertViewHas(
            'rankedBooks',
            function ($rankedBooks) use ($reviewedBook, $unreviewedBook) {
                return $rankedBooks->contains(
                    'id',
                    $reviewedBook->id
                )
                    && ! $rankedBooks->contains(
                        'id',
                        $unreviewedBook->id
                    )
                    && $rankedBooks->count() === 1;
            }
        );
    }

    /**
     * No.51
     *
     * 平均評価・レビュー件数・書籍名が異なる書籍を用意して
     * /ranking を表示し、平均評価の降順、同率時はレビュー件数の降順、
     * さらに同率時は書籍名の昇順で正しく順位付けされること。
     */
    public function test_ranking_is_sorted_by_rating_count_and_title(): void
    {
        $this->createBookWithReviews(
            '評価5.0・レビュー3件',
            [5, 5, 5]
        );

        $this->createBookWithReviews(
            '書籍B',
            [5, 5]
        );

        $this->createBookWithReviews(
            '書籍A',
            [5, 5]
        );

        $this->createBookWithReviews(
            '評価4.0・レビュー3件',
            [4, 4, 4]
        );

        $response = $this->get(route('ranking.index'));

        $response->assertOk();

        // 平均評価・レビュー件数・書籍名の順に並んでいること。
        $response->assertViewHas(
            'rankedBooks',
            function ($rankedBooks) {
                $actualTitles = $rankedBooks
                    ->pluck('title')
                    ->all();

                $expectedTitles = [
                    '評価5.0・レビュー3件',
                    '書籍A',
                    '書籍B',
                    '評価4.0・レビュー3件',
                ];

                return $actualTitles === $expectedTitles;
            }
        );
    }

    /**
     * No.52
     *
     * 11冊以上の対象書籍を用意して /ranking を表示し、
     * 上位10件のみ表示されること。
     */
    public function test_ranking_displays_only_top_ten_books(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            $this->createBookWithReviews(
                sprintf('ランキング書籍%02d', $i),
                array_fill(0, 13 - $i, 5)
            );
        }

        $response = $this->get(route('ranking.index'));

        $response->assertOk();

        $response->assertViewHas(
            'rankedBooks',
            function ($rankedBooks) {
                return $rankedBooks->count() === 10;
            }
        );

        for ($i = 1; $i <= 10; $i++) {
            $response->assertSee(
                sprintf('ランキング書籍%02d', $i)
            );
        }

        $response->assertDontSee('ランキング書籍11');
        $response->assertDontSee('ランキング書籍12');
    }
}
