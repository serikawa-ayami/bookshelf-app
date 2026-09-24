<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    /**
     * No.34
     * ログイン済みユーザーが有効な評価とコメントを投稿し、
     * レビューが保存され、書籍詳細画面にリダイレクトされ、
     * 成功メッセージが表示されること。
     */
    public function test_review_can_be_posted_successfully(): void
    {
        $user = User::create([
            'name' => 'レビュー投稿テストユーザー',
            'email' => 'review-post@example.com',
            'password' => 'password',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'レビュー投稿テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000206',
            'published_date' => '2026-09-01',
            'description' => 'レビュー投稿テスト用の書籍です。',
            'image_url' => null,
        ]);

        $response = $this->actingAs($user)
            ->post(route('reviews.store', $book), [
                'rating' => 5,
                'comment' => 'とても面白い本でした。',
            ]);

        $response->assertRedirect(route('books.show', $book))
            ->assertSessionHas(
                'success',
                'レビューを投稿しました。'
            );

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'とても面白い本でした。',
        ]);
    }

    /**
     * No.35
     * 無効な評価を投稿した場合、レビューが保存されず、
     * 投稿画面にリダイレクトされ、入力値とバリデーションエラーが
     * 保持されること。
     */
    public function test_review_post_fails_with_invalid_rating(): void
    {
        $user = User::create([
            'name' => 'レビュー投稿異常系ユーザー',
            'email' => 'review-post-error@example.com',
            'password' => 'password',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'レビュー投稿異常系テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000213',
            'published_date' => '2026-09-01',
            'description' => 'レビュー投稿異常系テスト用の書籍です。',
            'image_url' => null,
        ]);

        $response = $this->actingAs($user)
            ->from(route('books.show', $book))
            ->post(route('reviews.store', $book), [
                'rating' => 6,
                'comment' => '評価が範囲外のコメントです。',
            ]);

        $response->assertRedirect(route('books.show', $book))
            ->assertSessionHasErrors([
                'rating' => '評価は1〜5の範囲で入力してください。',
            ])
            ->assertSessionHas('_old_input.rating', 6)
            ->assertSessionHas(
                '_old_input.comment',
                '評価が範囲外のコメントです。'
            );

        $this->assertDatabaseMissing('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 6,
        ]);
    }

    /**
     * No.36
     * 自分のレビューを有効な評価とコメントに更新し、
     * 更新内容が保存され、書籍詳細画面にリダイレクトされ、
     * 成功メッセージが表示されること。
     */
    public function test_review_can_be_updated_successfully(): void
    {
        $user = User::create([
            'name' => 'レビュー編集テストユーザー',
            'email' => 'review-update@example.com',
            'password' => 'password',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'レビュー編集テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000220',
            'published_date' => '2026-09-01',
            'description' => 'レビュー編集テスト用の書籍です。',
            'image_url' => null,
        ]);

        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => '更新前のコメントです。',
        ]);

        $response = $this->actingAs($user)
            ->put(route('reviews.update', $review), [
                'rating' => 5,
                'comment' => '更新後のコメントです。',
            ]);

        $response->assertRedirect(route('books.show', $book))
            ->assertSessionHas(
                'success',
                'レビューを更新しました。'
            );

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '更新後のコメントです。',
        ]);
    }

    /**
     * No.37
     * 無効な評価でレビューを更新した場合、レビューが更新されず、
     * 編集画面にリダイレクトされ、入力値とバリデーションエラーが
     * 保持されること。
     */
    public function test_review_update_fails_with_invalid_rating(): void
    {
        $user = User::create([
            'name' => 'レビュー編集異常系ユーザー',
            'email' => 'review-update-error@example.com',
            'password' => 'password',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'レビュー編集異常系テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000237',
            'published_date' => '2026-09-01',
            'description' => 'レビュー編集異常系テスト用の書籍です。',
            'image_url' => null,
        ]);

        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => '更新前のコメントです。',
        ]);

        $response = $this->actingAs($user)
            ->from(route('reviews.edit', $review))
            ->put(route('reviews.update', $review), [
                'rating' => 0,
                'comment' => '更新できないコメントです。',
            ]);

        $response->assertRedirect(route('reviews.edit', $review))
            ->assertSessionHasErrors([
                'rating' => '評価は1〜5の範囲で入力してください。',
            ])
            ->assertSessionHas('_old_input.rating', 0)
            ->assertSessionHas(
                '_old_input.comment',
                '更新できないコメントです。'
            );

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 3,
            'comment' => '更新前のコメントです。',
        ]);
    }

    /**
     * No.38
     * 自分のレビューを削除し、レビューがDBから削除され、
     * 書籍詳細画面にリダイレクトされ、
     * 成功メッセージが表示されること。
     */
    public function test_review_can_be_deleted_successfully(): void
    {
        $user = User::create([
            'name' => 'レビュー削除テストユーザー',
            'email' => 'review-delete@example.com',
            'password' => 'password',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'レビュー削除テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000244',
            'published_date' => '2026-09-01',
            'description' => 'レビュー削除テスト用の書籍です。',
            'image_url' => null,
        ]);

        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '削除対象のレビューです。',
        ]);

        $response = $this->actingAs($user)
            ->delete(route('reviews.destroy', $review));

        $response->assertRedirect(route('books.show', $book))
            ->assertSessionHas(
                'success',
                'レビューを削除しました。'
            );

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }
}