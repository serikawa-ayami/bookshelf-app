<?php

namespace Tests\Feature\Api\V1;

use App\Models\Book;
use App\Models\Favorite;
use App\Models\Genre;
use App\Models\Review;
use App\Models\ReviewLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookDeleteApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * No.66
     * 存在する書籍を削除すると204 No Contentが返り、
     * レスポンスボディがなく、対象書籍と関連するreviews・favorites・book_genreが削除され、
     * 関連レビューの削除に伴ってreview_likesも削除されること。
     */
    public function test_destroy_deletes_book_and_related_data(): void
    {
        // テスト用ユーザーを作成する
        $user = User::factory()->create();

        // テスト用ジャンルを作成する
        $genre = Genre::create([
            'name' => '削除テストジャンル',
        ]);

        // テスト用書籍を作成する
        $book = Book::create([
            'user_id' => $user->id,
            'title' => '削除テスト書籍',
            'author' => '削除テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-09-27',
            'description' => '削除テスト用の説明',
            'image_url' => null,
        ]);

        // 書籍にジャンルを紐付ける
        $book->genres()->attach($genre->id);

        // テスト用レビューを作成する
        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '削除テスト用のレビュー',
        ]);

        // テスト用お気に入りを作成する
        $favorite = Favorite::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        // テスト用レビューいいねを作成する
        $reviewLike = ReviewLike::create([
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        // 書籍削除APIを実行する
        $response = $this->deleteJson(
            "/api/v1/books/{$book->id}"
        );

        // 204 No Contentが返り、レスポンスボディがないこと
        $response->assertNoContent();

        // 対象書籍が削除されていること
        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);

        // 関連レビューが削除されていること
        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);

        // 関連お気に入りが削除されていること
        $this->assertDatabaseMissing('favorites', [
            'id' => $favorite->id,
        ]);

        // 関連ジャンル紐付けが削除されていること
        $this->assertDatabaseMissing('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);

        // 関連レビューの削除に伴ってレビューいいねが削除されていること
        $this->assertDatabaseMissing('review_likes', [
            'id' => $reviewLike->id,
        ]);
    }

    /**
     * No.67
     * 存在しない書籍IDを指定してDELETE /api/v1/books/{book}を実行した場合に404 Not Foundが返り、
     * レスポンスのエラー値で「書籍が見つかりませんでした。」が表示されること。
     */
    public function test_destroy_returns_not_found_for_non_existing_book(): void
    {
        // 存在しない書籍IDを指定して削除APIを実行する
        $response = $this->deleteJson('/api/v1/books/999999');

        // 404 Not Foundが返ること
        $response->assertNotFound();

        // エラー値が指定された内容であること
        $response->assertJson([
            'error' => '書籍が見つかりませんでした。',
        ]);
    }
}
