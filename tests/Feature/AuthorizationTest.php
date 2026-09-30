<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * No.18
     * 他ユーザーの書籍の編集画面へ直接アクセスすると403になること。
     */
    public function test_other_users_book_edit_page_returns_forbidden(): void
    {
        $owner = User::create([
            'name' => '書籍所有者',
            'email' => 'owner@example.com',
            'password' => 'password',
        ]);

        $otherUser = User::create([
            'name' => '別ユーザー',
            'email' => 'other@example.com',
            'password' => 'password',
        ]);

        $book = Book::create([
            'user_id' => $owner->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000001',
            'published_date' => '2026-01-01',
            'description' => 'テスト用の書籍です。',
            'image_url' => 'https://example.com/book.jpg',
        ]);

        $this->actingAs($otherUser)
            ->get(route('books.edit', $book))
            ->assertForbidden();
    }

    /**
     * No.18
     * 他ユーザーの書籍を更新すると403となり、書籍が変更されないこと。
     */
    public function test_other_users_book_update_returns_forbidden_and_does_not_modify_book(): void
    {
        $owner = User::create([
            'name' => '書籍所有者',
            'email' => 'owner@example.com',
            'password' => 'password',
        ]);

        $otherUser = User::create([
            'name' => '別ユーザー',
            'email' => 'other@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => 'テストジャンル',
        ]);

        $book = Book::create([
            'user_id' => $owner->id,
            'title' => '元のタイトル',
            'author' => 'テスト著者',
            'isbn' => '9784000000002',
            'published_date' => '2026-01-01',
            'description' => 'テスト用の書籍です。',
            'image_url' => 'https://example.com/book.jpg',
        ]);

        $book->genres()->attach($genre->id);

        $response = $this->actingAs($otherUser)
            ->put(route('books.update', $book), [
                'title' => '変更後のタイトル',
                'author' => $book->author,
                'isbn' => $book->isbn,
                'published_date' => $book->published_date,
                'description' => $book->description,
                'image_url' => $book->image_url,
                'genres' => [$genre->id],
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '元のタイトル',
        ]);
    }

    /**
     * No.18
     * 他ユーザーの書籍を削除すると403となり、書籍が削除されないこと。
     */
    public function test_other_users_book_delete_returns_forbidden_and_does_not_delete_book(): void
    {
        $owner = User::create([
            'name' => '書籍所有者',
            'email' => 'owner@example.com',
            'password' => 'password',
        ]);

        $otherUser = User::create([
            'name' => '別ユーザー',
            'email' => 'other@example.com',
            'password' => 'password',
        ]);

        $book = Book::create([
            'user_id' => $owner->id,
            'title' => '削除対象テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000003',
            'published_date' => '2026-01-01',
            'description' => 'テスト用の書籍です。',
            'image_url' => 'https://example.com/book.jpg',
        ]);

        $response = $this->actingAs($otherUser)
            ->delete(route('books.destroy', $book));

        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }

    /**
     * No.19
     * 他ユーザーのレビューの編集画面へ直接アクセスすると403になること。
     */
    public function test_other_users_review_edit_page_returns_forbidden(): void
    {
        $owner = User::create([
            'name' => 'レビュー投稿者',
            'email' => 'review-owner@example.com',
            'password' => 'password',
        ]);

        $otherUser = User::create([
            'name' => '別ユーザー',
            'email' => 'review-other@example.com',
            'password' => 'password',
        ]);

        $book = Book::create([
            'user_id' => $owner->id,
            'title' => 'レビュー対象書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000011',
            'published_date' => '2026-01-01',
            'description' => 'テスト用の書籍です。',
            'image_url' => 'https://example.com/book.jpg',
        ]);

        $review = $book->reviews()->create([
            'user_id' => $owner->id,
            'rating' => 5,
            'comment' => 'テストレビューです。',
        ]);

        $this->actingAs($otherUser)
            ->get(route('reviews.edit', $review))
            ->assertForbidden();
    }

    /**
     * No.19
     * 他ユーザーのレビューを更新すると403となり、レビューが変更されないこと。
     */
    public function test_other_users_review_update_returns_forbidden_and_does_not_modify_review(): void
    {
        $owner = User::create([
            'name' => 'レビュー投稿者',
            'email' => 'review-owner@example.com',
            'password' => 'password',
        ]);

        $otherUser = User::create([
            'name' => '別ユーザー',
            'email' => 'review-other@example.com',
            'password' => 'password',
        ]);

        $book = Book::create([
            'user_id' => $owner->id,
            'title' => 'レビュー対象書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000012',
            'published_date' => '2026-01-01',
            'description' => 'テスト用の書籍です。',
            'image_url' => 'https://example.com/book.jpg',
        ]);

        $review = $book->reviews()->create([
            'user_id' => $owner->id,
            'rating' => 5,
            'comment' => '元のレビュー',
        ]);

        $response = $this->actingAs($otherUser)
            ->put(route('reviews.update', $review), [
                'rating' => 1,
                'comment' => '変更後のレビュー',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 5,
            'comment' => '元のレビュー',
        ]);
    }

    /**
     * No.19
     * 他ユーザーのレビューを削除すると403となり、レビューが削除されないこと。
     */
    public function test_other_users_review_delete_returns_forbidden_and_does_not_delete_review(): void
    {
        $owner = User::create([
            'name' => 'レビュー投稿者',
            'email' => 'review-owner@example.com',
            'password' => 'password',
        ]);

        $otherUser = User::create([
            'name' => '別ユーザー',
            'email' => 'review-other@example.com',
            'password' => 'password',
        ]);

        $book = Book::create([
            'user_id' => $owner->id,
            'title' => 'レビュー対象書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000013',
            'published_date' => '2026-01-01',
            'description' => 'テスト用の書籍です。',
            'image_url' => 'https://example.com/book.jpg',
        ]);

        $review = $book->reviews()->create([
            'user_id' => $owner->id,
            'rating' => 5,
            'comment' => '削除対象のレビュー',
        ]);

        $response = $this->actingAs($otherUser)
            ->delete(route('reviews.destroy', $review));

        $response->assertForbidden();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
        ]);
    }

    /**
     * No.20
     * 他ユーザーの書籍詳細画面に編集・削除UIが表示されないこと。
     */
    public function test_other_users_book_does_not_show_edit_or_delete_ui(): void
    {
        $owner = User::create([
            'name' => '書籍所有者',
            'email' => 'ui-book-owner@example.com',
            'password' => 'password',
        ]);

        $otherUser = User::create([
            'name' => '別ユーザー',
            'email' => 'ui-book-other@example.com',
            'password' => 'password',
        ]);

        $book = Book::create([
            'user_id' => $owner->id,
            'title' => 'UI確認用書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000021',
            'published_date' => '2026-01-01',
            'description' => 'UI確認用の書籍です。',
            'image_url' => 'https://example.com/book.jpg',
        ]);

        $response = $this->actingAs($otherUser)
            ->get(route('books.show', $book));

        $response->assertOk()
            ->assertDontSee(route('books.edit', $book))
            ->assertDontSee('action="'.route('books.destroy', $book).'"');
    }

    /**
     * No.20
     * 他ユーザーのレビューに編集・削除UIが表示されないこと。
     */
    public function test_other_users_review_does_not_show_edit_or_delete_ui(): void
    {
        $owner = User::create([
            'name' => 'レビュー投稿者',
            'email' => 'ui-review-owner@example.com',
            'password' => 'password',
        ]);

        $otherUser = User::create([
            'name' => '別ユーザー',
            'email' => 'ui-review-other@example.com',
            'password' => 'password',
        ]);

        $book = Book::create([
            'user_id' => $owner->id,
            'title' => 'レビューUI確認用書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000022',
            'published_date' => '2026-01-01',
            'description' => 'レビューUI確認用の書籍です。',
            'image_url' => 'https://example.com/book.jpg',
        ]);

        $review = $book->reviews()->create([
            'user_id' => $owner->id,
            'rating' => 5,
            'comment' => 'UI確認用レビュー',
        ]);

        $response = $this->actingAs($otherUser)
            ->get(route('books.show', $book));

        $response->assertOk()
            ->assertDontSee(route('reviews.edit', $review))
            ->assertDontSee('action="'.route('reviews.destroy', $review).'"');
    }
}
