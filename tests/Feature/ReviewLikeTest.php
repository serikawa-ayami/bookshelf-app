<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\ReviewLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewLikeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * テスト用のユーザー・書籍・レビューを作成する。
     */
    private function createReviewLikeTestData(): array
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
        ]);

        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'テストレビュー',
        ]);

        return [$user, $review];
    }

    /**
     * No.48
     * ログイン済みユーザーがレビューにいいねを追加し、
     * 再操作で解除した際に、いいね状態と件数が正しく切り替わること。
     */
    public function test_user_can_toggle_review_like(): void
    {
        [$user, $review] = $this->createReviewLikeTestData();

        $url = route('reviews.like', $review);

        // いいねを追加する。
        $this->actingAs($user)
            ->post($url)
            ->assertRedirect();

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        $this->assertSame(
            1,
            ReviewLike::where('review_id', $review->id)->count()
        );

        // 再操作していいねを解除する。
        $this->post($url)
            ->assertRedirect();

        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        $this->assertSame(
            0,
            ReviewLike::where('review_id', $review->id)->count()
        );
    }

    /**
     * No.49
     * 同一ユーザー・同一レビューに対して複数回操作しても、
     * いいねが重複せず、トグル状態と件数が正しく維持されること。
     */
    public function test_repeated_review_like_toggle_does_not_create_duplicates(): void
    {
        [$user, $review] = $this->createReviewLikeTestData();

        $url = route('reviews.like', $review);

        // 1回目の操作でいいねを追加する。
        $this->actingAs($user)
            ->post($url)
            ->assertRedirect();

        $this->assertSame(
            1,
            ReviewLike::where('review_id', $review->id)->count()
        );

        // 2回目の操作でいいねを解除する。
        $this->post($url)
            ->assertRedirect();

        $this->assertSame(
            0,
            ReviewLike::where('review_id', $review->id)->count()
        );

        // 3回目の操作で再びいいねを追加する。
        $this->post($url)
            ->assertRedirect();

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        $this->assertSame(
            1,
            ReviewLike::where('review_id', $review->id)->count()
        );

        // 同一ユーザー・同一レビューのいいねが重複していないこと。
        $this->assertSame(
            1,
            ReviewLike::where('user_id', $user->id)
                ->where('review_id', $review->id)
                ->count()
        );
    }
}
