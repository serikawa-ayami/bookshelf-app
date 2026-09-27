<?php

namespace Tests\Feature\Api\V1;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookUpdateApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * No.63
     * PUT /api/v1/books/{book}について、有効な書籍情報とgenresを送信すると
     * 200 OKが返り、書籍情報とジャンル関連が更新され、
     * BookListResource形式で更新結果が返り、genres・average_rating・review_countを含み、
     * user_idは更新対象とせず更新前の値が維持されること。
     */
    public function test_update_updates_book_and_returns_book_list_resource(): void
    {
        $user = User::factory()->create();

        $oldGenre = Genre::create([
            'name' => 'テストジャンル旧',
        ]);

        $newGenre = Genre::create([
            'name' => 'テストジャンル新',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '更新前のタイトル',
            'author' => '更新前の著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => '更新前の説明',
            'image_url' => null,
        ]);

        $book->genres()->attach($oldGenre->id);

        $response = $this->putJson("/api/v1/books/{$book->id}", [
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
            'isbn' => '9876543210123',
            'published_date' => '2026-09-27',
            'description' => '更新後の説明',
            'image_url' => null,
            'genres' => [$newGenre->id],
        ]);

        // 200 OKとBookListResource形式のレスポンスを確認する
        $response->assertOk()
            ->assertJsonPath('data.id', $book->id)
            ->assertJsonPath('data.title', '更新後のタイトル')
            ->assertJsonPath('data.author', '更新後の著者')
            ->assertJsonPath('data.isbn', '9876543210123')
            ->assertJsonPath('data.published_date', '2026-09-27')
            ->assertJsonPath('data.description', '更新後の説明')
            ->assertJsonPath('data.image_url', null)
            ->assertJsonPath('data.average_rating', null)
            ->assertJsonPath('data.review_count', 0)
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
            ]);

        // 更新後の書籍情報とuser_idの維持を確認する
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'user_id' => $user->id,
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
            'isbn' => '9876543210123',
            'published_date' => '2026-09-27',
            'description' => '更新後の説明',
        ]);

        // 新しいジャンルとの関連が登録されていることを確認する
        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $newGenre->id,
        ]);

        // 古いジャンルとの関連が解除されていることを確認する
        $this->assertDatabaseMissing('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $oldGenre->id,
        ]);
    }

    /**
     * No.64
     * PUT /api/v1/books/{book}について、不正な入力や他書籍と重複するISBNを送信した場合に
     * 422 Unprocessable Entityが返り、messageとerrorsを含む
     * 日本語のバリデーションエラー形式になり、書籍が更新されないこと。
     */
    public function test_update_returns_validation_errors_for_invalid_input(): void
    {
        $user = User::factory()->create();

        $genre = Genre::create([
            'name' => 'テストジャンル',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '更新前のタイトル',
            'author' => '更新前の著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => '更新前の説明',
            'image_url' => null,
        ]);

        $anotherBook = Book::create([
            'user_id' => $user->id,
            'title' => '別の書籍',
            'author' => '別の著者',
            'isbn' => '1111111111111',
            'published_date' => '2026-01-01',
            'description' => null,
            'image_url' => null,
        ]);

        $book->genres()->attach($genre->id);
        $anotherBook->genres()->attach($genre->id);

        $response = $this->putJson("/api/v1/books/{$book->id}", [
            'title' => '',
            'author' => '',
            'isbn' => $anotherBook->isbn,
            'published_date' => '不正な日付',
            'description' => str_repeat('あ', 1001),
            'image_url' => null,
            'genres' => [],
        ]);

        // 422とmessage・errorsを含むレスポンス形式を確認する
        $response->assertUnprocessable()
            ->assertJsonStructure([
                'message',
                'errors',
            ])
            ->assertJsonValidationErrors([
                'title',
                'author',
                'isbn',
                'published_date',
                'description',
                'genres',
            ]);

        // 要件シート「設計一覧」の書籍編集のメッセージと完全一致することを確認する
        $response->assertJsonPath(
            'errors.title.0',
            'タイトルは必須です。'
        );

        $response->assertJsonPath(
            'errors.author.0',
            '著者は必須です。'
        );

        $response->assertJsonPath(
            'errors.isbn.0',
            'このISBNはすでに登録されています。'
        );

        $response->assertJsonPath(
            'errors.published_date.0',
            '出版日は正しい日付を入力してください。'
        );

        $response->assertJsonPath(
            'errors.description.0',
            '説明は1000文字以内で入力してください。'
        );

        $response->assertJsonPath(
            'errors.genres.0',
            'ジャンルは必須です。'
        );

        // 書籍情報が更新されていないことを確認する
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'user_id' => $user->id,
            'title' => '更新前のタイトル',
            'author' => '更新前の著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => '更新前の説明',
        ]);

        // ジャンル関連が変更されていないことを確認する
        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }

    /**
     * No.65
     * 存在しない書籍IDを指定してPUT /api/v1/books/{book}を実行した場合に
     * 404 Not Foundが返り、レスポンスのエラー値で
     * 「書籍が見つかりませんでした。」が表示され、対象書籍が更新されないこと。
     */
    public function test_update_returns_not_found_for_non_existing_book(): void
    {
        $user = User::factory()->create();

        $genre = Genre::create([
            'name' => 'テストジャンル',
        ]);

        // 変更されないことを確認するための既存書籍を作成する
        $existingBook = Book::create([
            'user_id' => $user->id,
            'title' => '既存のタイトル',
            'author' => '既存の著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => '既存の説明',
            'image_url' => null,
        ]);

        $existingBook->genres()->attach($genre->id);

        // 存在しない書籍IDに対して更新リクエストを送信する
        $response = $this->putJson('/api/v1/books/999999', [
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
            'isbn' => '9876543210123',
            'published_date' => '2026-09-27',
            'description' => '更新後の説明',
            'image_url' => null,
            'genres' => [$genre->id],
        ]);

        // 404と指定されたエラー値を確認する
        $response->assertNotFound()
            ->assertJsonPath(
                'error',
                '書籍が見つかりませんでした。'
            );

        // 存在しない書籍が作成されていないことを確認する
        $this->assertDatabaseMissing('books', [
            'id' => 999999,
        ]);

        // 既存書籍の情報が変更されていないことを確認する
        $this->assertDatabaseHas('books', [
            'id' => $existingBook->id,
            'user_id' => $user->id,
            'title' => '既存のタイトル',
            'author' => '既存の著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'description' => '既存の説明',
        ]);

        // 既存書籍のジャンル関連が変更されていないことを確認する
        $this->assertDatabaseHas('book_genre', [
            'book_id' => $existingBook->id,
            'genre_id' => $genre->id,
        ]);
    }
}