<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Favorite;
use App\Models\Genre;
use App\Models\Review;
use App\Models\ReviewLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private int $userCounter = 1;

    private int $isbnCounter = 1;

    /**
     * テスト用のユーザーを作成する。
     */
    private function createTestUser(): User
    {
        $counter = $this->userCounter++;

        return User::create([
            'name' => "テストユーザー{$counter}",
            'email' => "data-integrity-{$counter}@example.com",
            'password' => bcrypt('password'),
        ]);
    }

    /**
     * テスト用の書籍を作成する。
     */
    private function createTestBook(User $user, string $title): Book
    {
        $isbn = str_pad(
            (string) $this->isbnCounter++,
            13,
            '0',
            STR_PAD_LEFT
        );

        return Book::create([
            'user_id' => $user->id,
            'title' => $title,
            'author' => 'テスト著者',
            'isbn' => $isbn,
            'published_date' => '2026-01-01',
        ]);
    }

    /**
     * テスト用のジャンルを作成する。
     */
    private function createTestGenre(string $name): Genre
    {
        return Genre::create([
            'name' => $name,
        ]);
    }

    /**
     * No.53-1
     *
     * 書籍にレビュー・お気に入り・いいね・ジャンル関連を登録して
     * 書籍を削除し、書籍と関連するデータが削除され、
     * 不整合なデータが残らないこと。
     */
    public function test_deleting_book_deletes_all_related_data(): void
    {
        // テスト用データを作成する。
        $owner = $this->createTestUser();
        $reviewer = $this->createTestUser();
        $favoriteUser = $this->createTestUser();
        $liker = $this->createTestUser();

        $book = $this->createTestBook($owner, '削除対象の書籍');

        $genre = $this->createTestGenre('削除対象のジャンル');

        $book->genres()->attach($genre->id);

        $review = Review::create([
            'user_id' => $reviewer->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'テストレビュー',
        ]);

        Favorite::create([
            'user_id' => $favoriteUser->id,
            'book_id' => $book->id,
        ]);

        ReviewLike::create([
            'user_id' => $liker->id,
            'review_id' => $review->id,
        ]);

        // 削除前に関連データが存在すること。
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
        ]);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $favoriteUser->id,
            'book_id' => $book->id,
        ]);

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $liker->id,
            'review_id' => $review->id,
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);

        // 書籍を削除する。
        $book->delete();

        // 書籍と関連するデータが削除されること。
        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $favoriteUser->id,
            'book_id' => $book->id,
        ]);

        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $liker->id,
            'review_id' => $review->id,
        ]);

        $this->assertDatabaseMissing('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);

        // 書籍に関連していないユーザーとジャンルは保持されること。
        $this->assertDatabaseHas('users', [
            'id' => $owner->id,
        ]);

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
        ]);
    }

    /**
     * No.53-2
     *
     * レビューにいいねを登録してレビューを削除し、
     * レビューと関連するいいねが削除され、
     * 対象書籍と他のレビューが保持されること。
     */
    public function test_deleting_review_deletes_related_likes(): void
    {
        // テスト用データを作成する。
        $owner = $this->createTestUser();
        $reviewer = $this->createTestUser();
        $liker = $this->createTestUser();
        $otherReviewer = $this->createTestUser();

        $book = $this->createTestBook($owner, 'レビュー削除対象の書籍');

        $review = Review::create([
            'user_id' => $reviewer->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '削除対象のレビュー',
        ]);

        $otherReview = Review::create([
            'user_id' => $otherReviewer->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '保持するレビュー',
        ]);

        ReviewLike::create([
            'user_id' => $liker->id,
            'review_id' => $review->id,
        ]);

        // 削除前にレビューといいねが存在すること。
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
        ]);

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $liker->id,
            'review_id' => $review->id,
        ]);

        // レビューを削除する。
        $review->delete();

        // 対象レビューと関連するいいねが削除されること。
        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);

        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $liker->id,
            'review_id' => $review->id,
        ]);

        // 書籍と他のレビューが保持されること。
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);

        $this->assertDatabaseHas('reviews', [
            'id' => $otherReview->id,
        ]);
    }

    /**
     * No.53-3
     *
     * 書籍とジャンルの関連付けを解除し、
     * book_genreの関連レコードだけが削除され、
     * 書籍・ジャンルと他の関連付けが保持されること。
     */
    public function test_detaching_genre_preserves_book_and_genre(): void
    {
        // テスト用データを作成する。
        $owner = $this->createTestUser();

        $book = $this->createTestBook($owner, 'ジャンル解除対象の書籍');

        $genre = $this->createTestGenre('解除対象のジャンル');
        $otherGenre = $this->createTestGenre('保持するジャンル');

        // 書籍に2つのジャンルを関連付ける。
        $book->genres()->attach([
            $genre->id,
            $otherGenre->id,
        ]);

        // 解除前に両方の関連付けが存在すること。
        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $otherGenre->id,
        ]);

        // 対象ジャンルとの関連付けを解除する。
        $book->genres()->detach($genre->id);

        // 対象の関連レコードだけが削除されること。
        $this->assertDatabaseMissing('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);

        // 他のジャンルとの関連付けが保持されること。
        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $otherGenre->id,
        ]);

        // 書籍と両方のジャンル自体が保持されること。
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
        ]);

        $this->assertDatabaseHas('genres', [
            'id' => $otherGenre->id,
        ]);
    }
}