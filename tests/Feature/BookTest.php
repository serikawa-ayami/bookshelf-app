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

class BookTest extends TestCase
{
    use RefreshDatabase;

    /**
     * No.26
     * GET / にアクセスし、書籍一覧が表示され、
     * タイトル等の書籍情報とジャンルが表示されること。
     */
    public function test_book_list_displays_books_and_genres(): void
    {
        $user = User::create([
            'name' => '書籍一覧テストユーザー',
            'email' => 'book-list@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => 'テストジャンル',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000107',
            'published_date' => '2026-09-01',
            'description' => 'テスト用の説明です。',
            'image_url' => null,
        ]);

        $book->genres()->attach($genre->id);

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('テスト書籍')
            ->assertSee('テスト著者')
            ->assertSee('テストジャンル');
    }

    /**
     * No.27
     * 書籍詳細を表示し、title/author/ISBN/published_date/description/image/genres/reviews/like countを確認する。
     * 仕様で定めた書籍情報・関連情報が正しく表示されること。
     */
    public function test_book_detail_displays_book_information_reviews_and_like_count(): void
    {
        $user = User::create([
            'name' => '書籍詳細テストユーザー',
            'email' => 'book-detail@example.com',
            'password' => 'password',
        ]);

        $reviewUser = User::create([
            'name' => 'レビュー投稿ユーザー',
            'email' => 'review-user@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '詳細テストジャンル',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '詳細テスト書籍',
            'author' => '詳細テスト著者',
            'isbn' => '9784000000114',
            'published_date' => '2026-09-10',
            'description' => '詳細テスト用の説明です。',
            'image_url' => 'https://example.com/book.jpg',
        ]);

        $book->genres()->attach($genre->id);

        $review = Review::create([
            'user_id' => $reviewUser->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '詳細テストレビューです。',
        ]);

        $likeUser = User::create([
            'name' => 'いいねユーザー',
            'email' => 'like-user@example.com',
            'password' => 'password',
        ]);

        ReviewLike::create([
            'user_id' => $likeUser->id,
            'review_id' => $review->id,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('books.show', $book));

        $response->assertOk()
            ->assertSee('詳細テスト書籍')
            ->assertSee('詳細テスト著者')
            ->assertSee('9784000000114')
            ->assertSee('2026-09-10')
            ->assertSee('詳細テスト用の説明です。')
            ->assertSee('https://example.com/book.jpg')
            ->assertSee('詳細テストジャンル')
            ->assertSee('レビュー投稿ユーザー')
            ->assertSee('5')
            ->assertSee('詳細テストレビューです。')
            ->assertSee('いいね (1)');
    }

    /**
     * No.28
     * ログイン済みユーザーが /books/create にアクセスし、登録フォームが表示され、
     * タイトル・著者・ISBN・出版日・説明・画像URL・複数ジャンルを入力できること。
     */
    public function test_book_create_screen_displays_registration_form(): void
    {
        $user = User::create([
            'name' => '書籍登録画面テストユーザー',
            'email' => 'book-create-screen@example.com',
            'password' => 'password',
        ]);

        $genre1 = Genre::create([
            'name' => '登録画面ジャンル1',
        ]);

        $genre2 = Genre::create([
            'name' => '登録画面ジャンル2',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('books.create'));

        $response->assertOk()
            ->assertSee('書籍の登録')
            ->assertSee('name="title"', false)
            ->assertSee('name="author"', false)
            ->assertSee('name="isbn"', false)
            ->assertSee('name="published_date"', false)
            ->assertSee('name="description"', false)
            ->assertSee('name="image_url"', false)
            ->assertSee('name="genres[]"', false)
            ->assertSee('登録画面ジャンル1')
            ->assertSee('登録画面ジャンル2');
    }

    /**
     * No.29
     * 有効な書籍情報＋1件以上のジャンルでPOST /booksし、
     * 書籍とジャンル関連が保存され、書籍一覧へ遷移し、
     * 「書籍を登録しました。」が表示されること。
     */
    public function test_book_can_be_registered_successfully(): void
    {
        $user = User::create([
            'name' => '書籍登録テストユーザー',
            'email' => 'book-store@example.com',
            'password' => 'password',
        ]);

        $genre1 = Genre::create([
            'name' => '登録テストジャンル1',
        ]);

        $genre2 = Genre::create([
            'name' => '登録テストジャンル2',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('books.store'), [
            'title' => '登録テスト書籍',
            'author' => '登録テスト著者',
            'isbn' => '9784000000121',
            'published_date' => '2026-09-20',
            'description' => '登録テスト用の説明です。',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [$genre1->id, $genre2->id],
        ]);

        $response->assertRedirect(route('books.index'));
        $response->assertSessionHas('success', '書籍を登録しました。');

        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => '登録テスト書籍',
            'author' => '登録テスト著者',
            'isbn' => '9784000000121',
            'published_date' => '2026-09-20',
            'description' => '登録テスト用の説明です。',
            'image_url' => 'https://example.com/book.jpg',
        ]);

        $book = Book::where('isbn', '9784000000121')->first();

        $this->assertNotNull($book);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre1->id,
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre2->id,
        ]);
    }

    /**
     * 書籍の所有者がタイトルを未入力にして書籍更新を行い、
     * バリデーションエラーとなり、書籍情報が更新されないこと。
     */
    public function test_book_update_fails_when_title_is_empty(): void
    {
        $user = User::create([
            'name' => '書籍更新エラーテストユーザー',
            'email' => 'book-update-error@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '更新エラーテストジャンル',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9784000000145',
            'published_date' => '2026-09-20',
            'description' => '更新前の説明です。',
            'image_url' => 'https://example.com/before.jpg',
        ]);

        $book->genres()->attach($genre->id);

        $this->actingAs($user);

        $response = $this->put(route('books.update', $book), [
            'title' => '',
            'author' => '更新後著者',
            'isbn' => '9784000000146',
            'published_date' => '2026-09-21',
            'description' => '更新後の説明です。',
            'image_url' => 'https://example.com/after.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'title' => 'タイトルは必須です。',
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9784000000145',
        ]);
    }

    /**
     * 書籍の所有者が著者を未入力にして書籍更新を行い、
     * バリデーションエラーとなり、書籍情報が更新されないこと。
     */
    public function test_book_update_fails_when_author_is_empty(): void
    {
        $user = User::create([
            'name' => '書籍更新エラーテストユーザー',
            'email' => 'book-update-author-error@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '更新エラーテストジャンル',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9784000000152',
            'published_date' => '2026-09-20',
            'description' => '更新前の説明です。',
            'image_url' => 'https://example.com/before.jpg',
        ]);

        $book->genres()->attach($genre->id);

        $this->actingAs($user);

        $response = $this->put(route('books.update', $book), [
            'title' => '更新後タイトル',
            'author' => '',
            'isbn' => '9784000000153',
            'published_date' => '2026-09-21',
            'description' => '更新後の説明です。',
            'image_url' => 'https://example.com/after.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'author' => '著者は必須です。',
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9784000000152',
        ]);
    }

    /**
     * 書籍の所有者がISBNを未入力にして書籍更新を行い、
     * バリデーションエラーとなり、書籍情報が更新されないこと。
     */
    public function test_book_update_fails_when_isbn_is_empty(): void
    {
        $user = User::create([
            'name' => '書籍更新エラーテストユーザー',
            'email' => 'book-update-isbn-error@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '更新エラーテストジャンル',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9784000000169',
            'published_date' => '2026-09-20',
            'description' => '更新前の説明です。',
            'image_url' => 'https://example.com/before.jpg',
        ]);

        $book->genres()->attach($genre->id);

        $this->actingAs($user);

        $response = $this->put(route('books.update', $book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'isbn' => '',
            'published_date' => '2026-09-21',
            'description' => '更新後の説明です。',
            'image_url' => 'https://example.com/after.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'isbn' => 'ISBNは必須です。',
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9784000000169',
        ]);
    }

    /**
     * 書籍の所有者が出版日を未入力にして書籍更新を行い、
     * バリデーションエラーとなり、書籍情報が更新されないこと。
     */
    public function test_book_update_fails_when_published_date_is_empty(): void
    {
        $user = User::create([
            'name' => '書籍更新エラーテストユーザー',
            'email' => 'book-update-date-error@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '更新エラーテストジャンル',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9784000000176',
            'published_date' => '2026-09-20',
            'description' => '更新前の説明です。',
            'image_url' => 'https://example.com/before.jpg',
        ]);

        $book->genres()->attach($genre->id);

        $this->actingAs($user);

        $response = $this->put(route('books.update', $book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'isbn' => '9784000000183',
            'published_date' => '',
            'description' => '更新後の説明です。',
            'image_url' => 'https://example.com/after.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'published_date' => '出版日は必須です。',
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9784000000176',
        ]);
    }

    /**
     * 書籍の所有者が13桁以外のISBNを入力して書籍更新を行い、
     * バリデーションエラーとなり、書籍情報が更新されないこと。
     */
    public function test_book_update_fails_when_isbn_is_not_13_digits(): void
    {
        $user = User::create([
            'name' => '書籍更新エラーテストユーザー',
            'email' => 'book-update-isbn-digits-error@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '更新エラーテストジャンル',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9784000000190',
            'published_date' => '2026-09-20',
            'description' => '更新前の説明です。',
            'image_url' => 'https://example.com/before.jpg',
        ]);

        $book->genres()->attach($genre->id);

        $this->actingAs($user);

        $response = $this->put(route('books.update', $book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'isbn' => '123456789012',
            'published_date' => '2026-09-21',
            'description' => '更新後の説明です。',
            'image_url' => 'https://example.com/after.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'isbn' => 'ISBNは13桁の数字で入力してください。',
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9784000000190',
        ]);
    }

    /**
     * 書籍の所有者が他の書籍と重複するISBNを入力して更新を行い、
     * バリデーションエラーとなり、書籍情報が更新されないこと。
     */
    public function test_book_update_fails_when_isbn_already_exists(): void
    {
        $user = User::create([
            'name' => '書籍更新エラーテストユーザー',
            'email' => 'book-update-isbn-duplicate@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '更新エラーテストジャンル',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '更新対象の書籍',
            'author' => '更新前著者',
            'isbn' => '9784000000206',
            'published_date' => '2026-09-20',
            'description' => '更新前の説明です。',
            'image_url' => 'https://example.com/before.jpg',
        ]);

        Book::create([
            'user_id' => $user->id,
            'title' => '重複ISBNを持つ書籍',
            'author' => '別の著者',
            'isbn' => '9784000000213',
            'published_date' => '2026-09-19',
            'description' => '別の書籍の説明です。',
            'image_url' => 'https://example.com/other.jpg',
        ]);

        $book->genres()->attach($genre->id);

        $this->actingAs($user);

        $response = $this->put(route('books.update', $book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'isbn' => '9784000000213',
            'published_date' => '2026-09-21',
            'description' => '更新後の説明です。',
            'image_url' => 'https://example.com/after.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'isbn' => 'このISBNはすでに登録されています。',
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新対象の書籍',
            'author' => '更新前著者',
            'isbn' => '9784000000206',
        ]);
    }

    /**
     * 書籍の所有者が1000文字を超える説明文を入力して更新を行い、
     * バリデーションエラーとなり、書籍情報が更新されないこと。
     */
    public function test_book_update_fails_when_description_exceeds_1000_characters(): void
    {
        $user = User::create([
            'name' => '書籍更新エラーテストユーザー',
            'email' => 'book-update-description-error@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '更新エラーテストジャンル',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9784000000220',
            'published_date' => '2026-09-20',
            'description' => '更新前の説明です。',
            'image_url' => 'https://example.com/before.jpg',
        ]);

        $book->genres()->attach($genre->id);

        $this->actingAs($user);

        $response = $this->put(route('books.update', $book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'isbn' => '9784000000227',
            'published_date' => '2026-09-21',
            'description' => str_repeat('あ', 1001),
            'image_url' => 'https://example.com/after.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'description' => '説明は1000文字以内で入力してください。',
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9784000000220',
        ]);
    }

    /**
     * 書籍の所有者が不正な画像URLを入力して書籍更新を行い、
     * バリデーションエラーとなり、書籍情報が更新されないこと。
     */
    public function test_book_update_fails_when_image_url_is_invalid(): void
    {
        $user = User::create([
            'name' => '書籍更新エラーテストユーザー',
            'email' => 'book-update-image-url-error@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '更新エラーテストジャンル',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9784000000237',
            'published_date' => '2026-09-20',
            'description' => '更新前の説明です。',
            'image_url' => 'https://example.com/before.jpg',
        ]);

        $book->genres()->attach($genre->id);

        $this->actingAs($user);

        $response = $this->put(route('books.update', $book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'isbn' => '9784000000244',
            'published_date' => '2026-09-21',
            'description' => '更新後の説明です。',
            'image_url' => 'invalid-url',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'image_url' => '画像URLの形式が正しくありません。',
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9784000000237',
        ]);
    }

    /**
     * 書籍の所有者が255文字を超える画像URLを入力して更新を行い、
     * バリデーションエラーとなり、書籍情報が更新されないこと。
     */
    public function test_book_update_fails_when_image_url_exceeds_255_characters(): void
    {
        $user = User::create([
            'name' => '書籍更新エラーテストユーザー',
            'email' => 'book-update-image-url-length-error@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '更新エラーテストジャンル',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9784000000251',
            'published_date' => '2026-09-20',
            'description' => '更新前の説明です。',
            'image_url' => 'https://example.com/before.jpg',
        ]);

        $book->genres()->attach($genre->id);

        $this->actingAs($user);

        $response = $this->put(route('books.update', $book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'isbn' => '9784000000268',
            'published_date' => '2026-09-21',
            'description' => '更新後の説明です。',
            'image_url' => 'https://example.com/'.str_repeat('a', 240),
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'image_url' => '画像URLは255文字以内で入力してください。',
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9784000000251',
        ]);
    }

    /**
     * 書籍の所有者がジャンルを選択せずに書籍更新を行い、
     * バリデーションエラーとなり、書籍情報が更新されないこと。
     */
    public function test_book_update_fails_when_genres_are_empty(): void
    {
        $user = User::create([
            'name' => '書籍更新エラーテストユーザー',
            'email' => 'book-update-genres-error@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '更新エラーテストジャンル',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9784000000275',
            'published_date' => '2026-09-20',
            'description' => '更新前の説明です。',
            'image_url' => 'https://example.com/before.jpg',
        ]);

        $book->genres()->attach($genre->id);

        $this->actingAs($user);

        $response = $this->put(route('books.update', $book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'isbn' => '9784000000282',
            'published_date' => '2026-09-21',
            'description' => '更新後の説明です。',
            'image_url' => 'https://example.com/after.jpg',
            'genres' => [],
        ]);

        $response->assertSessionHasErrors([
            'genres' => 'ジャンルは必須です。',
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9784000000275',
        ]);
    }

    /**
     * ジャンルが配列以外の場合、バリデーションエラーになること
     */
    public function test_book_update_fails_when_genres_are_not_array(): void
    {
        $user = User::create([
            'name' => '書籍更新エラーテストユーザー',
            'email' => 'book-update-genres-not-array@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '更新エラーテストジャンル',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9784000000276',
            'published_date' => '2026-09-20',
            'description' => '更新前の説明です。',
            'image_url' => 'https://example.com/before.jpg',
        ]);

        $book->genres()->attach($genre->id);

        $this->actingAs($user);

        $response = $this->put(route('books.update', $book), [
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
            'isbn' => '9784000000001',
            'published_date' => '2025-01-01',
            'description' => '更新後の説明',
            'image_url' => null,
            'genres' => 'invalid',
        ]);

        $response->assertSessionHasErrors([
            'genres' => 'ジャンルの形式が正しくありません。',
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9784000000276',
        ]);
    }

    /**
     * 存在しないジャンルIDを指定した場合、バリデーションエラーとなり、
     * 書籍情報が更新されないこと。
     */
    public function test_book_update_fails_when_genre_id_does_not_exist(): void
    {
        $user = User::create([
            'name' => '書籍更新エラーテストユーザー',
            'email' => 'book-update-invalid-genre@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '更新エラーテストジャンル',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9784000000277',
            'published_date' => '2026-09-20',
            'description' => '更新前の説明です。',
            'image_url' => 'https://example.com/before.jpg',
        ]);

        $book->genres()->attach($genre->id);

        $this->actingAs($user);

        $response = $this->put(route('books.update', $book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'isbn' => '9784000000277',
            'published_date' => '2026-09-21',
            'description' => '更新後の説明です。',
            'image_url' => 'https://example.com/after.jpg',
            'genres' => [999999],
        ]);

        $response->assertSessionHasErrors([
            'genres.0' => '選択したジャンルが存在しません。',
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9784000000277',
        ]);
    }

    /**
     * 書籍の所有者が書籍情報を更新し、
     * 書籍情報とジャンルが保存され、
     * 書籍詳細画面へ遷移し、成功メッセージが表示されること。
     */
    public function test_book_can_be_updated_successfully(): void
    {
        $user = User::create([
            'name' => '書籍更新テストユーザー',
            'email' => 'book-update@example.com',
            'password' => 'password',
        ]);

        $genre1 = Genre::create([
            'name' => '更新前ジャンル',
        ]);

        $genre2 = Genre::create([
            'name' => '更新後ジャンル',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9784000000122',
            'published_date' => '2026-09-20',
            'description' => '更新前の説明です。',
            'image_url' => 'https://example.com/before.jpg',
        ]);

        // 更新前のジャンルを登録する
        $book->genres()->attach($genre1->id);

        // 書籍の所有者として更新処理を実行する
        $response = $this->actingAs($user)
            ->put(route('books.update', $book), [
                'title' => '更新後タイトル',
                'author' => '更新後著者',
                'isbn' => '9784000000123',
                'published_date' => '2026-09-21',
                'description' => '更新後の説明です。',
                'image_url' => 'https://example.com/after.jpg',
                'genres' => [$genre2->id],
            ]);

        // 書籍詳細画面へ遷移し、成功メッセージが表示されること
        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', '書籍を更新しました。');

        // 書籍情報が更新されていること
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'user_id' => $user->id,
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'isbn' => '9784000000123',
            'published_date' => '2026-09-21',
            'description' => '更新後の説明です。',
            'image_url' => 'https://example.com/after.jpg',
        ]);

        // 更新後のジャンルが登録され、更新前のジャンルが解除されていること
        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre2->id,
        ]);

        $this->assertDatabaseMissing('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre1->id,
        ]);
    }

    /**
     * タイトルが255文字を超えた場合、バリデーションエラーとなり、
     * 書籍情報が更新されないこと。
     */
    public function test_book_update_fails_when_title_exceeds_255_characters(): void
    {
        $user = User::create([
            'name' => '書籍更新エラーテストユーザー',
            'email' => 'book-update-title-max@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '更新エラーテストジャンル',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9784000000278',
            'published_date' => '2026-09-20',
            'description' => '更新前の説明です。',
            'image_url' => 'https://example.com/before.jpg',
        ]);

        $book->genres()->attach($genre->id);

        $this->actingAs($user);

        $response = $this->put(route('books.update', $book), [
            'title' => str_repeat('あ', 256),
            'author' => '更新後著者',
            'isbn' => '9784000000278',
            'published_date' => '2026-09-21',
            'description' => '更新後の説明です。',
            'image_url' => 'https://example.com/after.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'title' => 'タイトルは255文字以内で入力してください。',
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9784000000278',
        ]);
    }

    /**
     * 著者が255文字を超えた場合、バリデーションエラーとなり、
     * 書籍情報が更新されないこと。
     */
    public function test_book_update_fails_when_author_exceeds_255_characters(): void
    {
        $user = User::create([
            'name' => '書籍更新エラーテストユーザー',
            'email' => 'book-update-author-max@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '更新エラーテストジャンル',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9784000000279',
            'published_date' => '2026-09-20',
            'description' => '更新前の説明です。',
            'image_url' => 'https://example.com/before.jpg',
        ]);

        $book->genres()->attach($genre->id);

        $this->actingAs($user);

        $response = $this->put(route('books.update', $book), [
            'title' => '更新後タイトル',
            'author' => str_repeat('あ', 256),
            'isbn' => '9784000000279',
            'published_date' => '2026-09-21',
            'description' => '更新後の説明です。',
            'image_url' => 'https://example.com/after.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'author' => '著者は255文字以内で入力してください。',
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9784000000279',
        ]);
    }

    /**
     * 出版日に不正な日付を指定した場合、バリデーションエラーとなり、
     * 書籍情報が更新されないこと。
     */
    public function test_book_update_fails_when_published_date_is_invalid(): void
    {
        $user = User::create([
            'name' => '書籍更新エラーテストユーザー',
            'email' => 'book-update-date-invalid@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '更新エラーテストジャンル',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9784000000280',
            'published_date' => '2026-09-20',
            'description' => '更新前の説明です。',
            'image_url' => 'https://example.com/before.jpg',
        ]);

        $book->genres()->attach($genre->id);

        $this->actingAs($user);

        $response = $this->put(route('books.update', $book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'isbn' => '9784000000280',
            'published_date' => 'invalid-date',
            'description' => '更新後の説明です。',
            'image_url' => 'https://example.com/after.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'published_date',
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9784000000280',
            'published_date' => '2026-09-20',
        ]);
    }

    /**
     * 説明にnullを指定した場合、正常に書籍情報を更新できること。
     */
    public function test_book_can_be_updated_with_null_description(): void
    {
        $user = User::create([
            'name' => '書籍更新テストユーザー',
            'email' => 'book-update-null-description@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '更新テストジャンル',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9784000000281',
            'published_date' => '2026-09-20',
            'description' => '更新前の説明です。',
            'image_url' => 'https://example.com/before.jpg',
        ]);

        $book->genres()->attach($genre->id);

        $this->actingAs($user);

        $response = $this->put(route('books.update', $book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'isbn' => '9784000000281',
            'published_date' => '2026-09-21',
            'description' => null,
            'image_url' => 'https://example.com/after.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', '書籍を更新しました。');

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後タイトル',
            'description' => null,
        ]);
    }

    /**
     * 画像URLにnullを指定した場合、正常に書籍情報を更新できること。
     */
    public function test_book_can_be_updated_with_null_image_url(): void
    {
        $user = User::create([
            'name' => '書籍更新テストユーザー',
            'email' => 'book-update-null-image-url@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '更新テストジャンル',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9784000000282',
            'published_date' => '2026-09-20',
            'description' => '更新前の説明です。',
            'image_url' => 'https://example.com/before.jpg',
        ]);

        $book->genres()->attach($genre->id);

        $this->actingAs($user);

        $response = $this->put(route('books.update', $book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'isbn' => '9784000000282',
            'published_date' => '2026-09-21',
            'description' => '更新後の説明です。',
            'image_url' => null,
            'genres' => [$genre->id],
        ]);

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', '書籍を更新しました。');

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後タイトル',
            'image_url' => null,
        ]);
    }

    /**
     * No.30
     * タイトルを未入力にして書籍登録を行い、
     * バリデーションエラーとなり、書籍が登録されないこと。
     */
    public function test_book_registration_fails_when_title_is_empty(): void
    {
        $user = User::create([
            'name' => '書籍登録エラーテストユーザー',
            'email' => 'book-error@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '登録エラーテストジャンル',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('books.store'), [
            'title' => '',
            'author' => '登録エラーテスト著者',
            'isbn' => '9784000000138',
            'published_date' => '2026-09-20',
            'description' => '登録エラーテスト用の説明です。',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'title' => 'タイトルは必須です。',
        ]);

        $this->assertDatabaseMissing('books', [
            'isbn' => '9784000000138',
        ]);
    }

    /**
     * No.30
     * 著者を未入力にして書籍登録を行い、
     * バリデーションエラーとなり、書籍が登録されないこと。
     */
    public function test_book_registration_fails_when_author_is_empty(): void
    {
        $user = User::create([
            'name' => '著者未入力テストユーザー',
            'email' => 'book-author-error@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '著者未入力テストジャンル',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('books.store'), [
            'title' => '著者未入力テスト書籍',
            'author' => '',
            'isbn' => '9784000000145',
            'published_date' => '2026-09-20',
            'description' => 'テスト用の説明です。',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'author' => '著者は必須です。',
        ]);

        $this->assertDatabaseMissing('books', [
            'isbn' => '9784000000145',
        ]);
    }

    /**
     * No.30
     * ISBNを未入力にして書籍登録を行い、
     * バリデーションエラーとなり、書籍が登録されないこと。
     */
    public function test_book_registration_fails_when_isbn_is_empty(): void
    {
        $user = User::create([
            'name' => 'ISBN未入力テストユーザー',
            'email' => 'book-isbn-error@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => 'ISBN未入力テストジャンル',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('books.store'), [
            'title' => 'ISBN未入力テスト書籍',
            'author' => 'ISBN未入力テスト著者',
            'isbn' => '',
            'published_date' => '2026-09-20',
            'description' => 'テスト用の説明です。',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'isbn' => 'ISBNは必須です。',
        ]);

        $this->assertDatabaseMissing('books', [
            'title' => 'ISBN未入力テスト書籍',
        ]);
    }

    /**
     * No.30
     * 出版日を未入力にして書籍登録を行い、
     * バリデーションエラーとなり、書籍が登録されないこと。
     */
    public function test_book_registration_fails_when_published_date_is_empty(): void
    {
        $user = User::create([
            'name' => '出版日未入力テストユーザー',
            'email' => 'book-date-error@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '出版日未入力テストジャンル',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('books.store'), [
            'title' => '出版日未入力テスト書籍',
            'author' => '出版日未入力テスト著者',
            'isbn' => '9784000000152',
            'published_date' => '',
            'description' => 'テスト用の説明です。',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'published_date' => '出版日は必須です。',
        ]);

        $this->assertDatabaseMissing('books', [
            'isbn' => '9784000000152',
        ]);
    }

    /**
     * No.30
     * ジャンルを未選択にして書籍登録を行い、
     * バリデーションエラーとなり、書籍が登録されないこと。
     */
    public function test_book_registration_fails_when_genres_are_empty(): void
    {
        $user = User::create([
            'name' => 'ジャンル未選択テストユーザー',
            'email' => 'book-genre-error@example.com',
            'password' => 'password',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('books.store'), [
            'title' => 'ジャンル未選択テスト書籍',
            'author' => 'ジャンル未選択テスト著者',
            'isbn' => '9784000000169',
            'published_date' => '2026-09-20',
            'description' => 'テスト用の説明です。',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [],
        ]);

        $response->assertSessionHasErrors([
            'genres' => 'ジャンルは必須です。',
        ]);

        $this->assertDatabaseMissing('books', [
            'isbn' => '9784000000169',
        ]);
    }

    /**
     * No.30
     * 12桁のISBNで書籍登録を行い、
     * バリデーションエラーとなり、書籍が登録されないこと。
     */
    public function test_book_registration_fails_when_isbn_is_not_13_digits(): void
    {
        $user = User::create([
            'name' => 'ISBN桁数テストユーザー',
            'email' => 'book-isbn-length-error@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => 'ISBN桁数テストジャンル',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('books.store'), [
            'title' => 'ISBN桁数テスト書籍',
            'author' => 'ISBN桁数テスト著者',
            'isbn' => '978400000017',
            'published_date' => '2026-09-20',
            'description' => 'テスト用の説明です。',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'isbn' => 'ISBNは13桁の数字で入力してください。',
        ]);

        $this->assertDatabaseMissing('books', [
            'title' => 'ISBN桁数テスト書籍',
        ]);
    }

    /**
     * No.30
     * 登録済みのISBNで書籍登録を行い、
     * バリデーションエラーとなり、書籍が登録されないこと。
     */
    public function test_book_registration_fails_when_isbn_already_exists(): void
    {
        $user = User::create([
            'name' => 'ISBN重複テストユーザー',
            'email' => 'book-isbn-duplicate@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => 'ISBN重複テストジャンル',
        ]);

        Book::create([
            'user_id' => $user->id,
            'title' => '登録済みテスト書籍',
            'author' => '登録済みテスト著者',
            'isbn' => '9784000000183',
            'published_date' => '2026-09-01',
            'description' => '登録済みの書籍です。',
            'image_url' => null,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('books.store'), [
            'title' => 'ISBN重複テスト書籍',
            'author' => 'ISBN重複テスト著者',
            'isbn' => '9784000000183',
            'published_date' => '2026-09-20',
            'description' => 'テスト用の説明です。',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'isbn' => 'このISBNはすでに登録されています。',
        ]);

        $this->assertDatabaseMissing('books', [
            'title' => 'ISBN重複テスト書籍',
        ]);
    }

    /**
     * No.30
     * 不正な出版日で書籍登録を行い、
     * バリデーションエラーとなり、書籍が登録されないこと。
     */
    public function test_book_registration_fails_when_published_date_is_invalid(): void
    {
        $user = User::create([
            'name' => '出版日形式テストユーザー',
            'email' => 'book-date-format-error@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '出版日形式テストジャンル',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('books.store'), [
            'title' => '出版日形式テスト書籍',
            'author' => '出版日形式テスト著者',
            'isbn' => '9784000000190',
            'published_date' => 'invalid-date',
            'description' => 'テスト用の説明です。',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'published_date' => '出版日は正しい日付を入力してください。',
        ]);

        $this->assertDatabaseMissing('books', [
            'isbn' => '9784000000190',
        ]);
    }

    /**
     * No.30
     * 不正な画像URLで書籍登録を行い、
     * バリデーションエラーとなり、書籍が登録されないこと。
     */
    public function test_book_registration_fails_when_image_url_is_invalid(): void
    {
        $user = User::create([
            'name' => '画像URLテストユーザー',
            'email' => 'book-image-url-error@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '画像URLテストジャンル',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('books.store'), [
            'title' => '画像URLテスト書籍',
            'author' => '画像URLテスト著者',
            'isbn' => '9784000000206',
            'published_date' => '2025-01-01',
            'description' => 'テスト用の説明です。',
            'image_url' => 'invalid-url',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'image_url' => '画像URLの形式が正しくありません。',
        ]);

        $this->assertDatabaseMissing('books', [
            'isbn' => '9784000000206',
        ]);
    }

    /**
     * No.30
     * 255文字を超える画像URLで書籍登録を行い、
     * バリデーションエラーとなり、書籍が登録されないこと。
     */
    public function test_book_registration_fails_when_image_url_exceeds_255_characters(): void
    {
        $user = User::create([
            'name' => '画像URL文字数テストユーザー',
            'email' => 'book-image-url-length-error@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '画像URL文字数テストジャンル',
        ]);

        $this->actingAs($user);

        $longImageUrl = 'https://example.com/'.str_repeat('a', 250);

        $response = $this->post(route('books.store'), [
            'title' => '画像URL文字数テスト書籍',
            'author' => '画像URL文字数テスト著者',
            'isbn' => '9784000000213',
            'published_date' => '2025-01-01',
            'description' => 'テスト用の説明です。',
            'image_url' => $longImageUrl,
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'image_url' => '画像URLは255文字以内で入力してください。',
        ]);

        $this->assertDatabaseMissing('books', [
            'isbn' => '9784000000213',
        ]);
    }

    /**
     * No.30
     * 存在しないジャンルIDを指定して書籍登録を行い、
     * バリデーションエラーとなり、書籍が登録されないこと。
     */
    public function test_book_registration_fails_when_genre_does_not_exist(): void
    {
        $user = User::create([
            'name' => '存在しないジャンルテストユーザー',
            'email' => 'book-invalid-genre@example.com',
            'password' => 'password',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('books.store'), [
            'title' => '存在しないジャンルテスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000220',
            'published_date' => '2025-01-01',
            'description' => 'テスト用の説明です。',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [999999],
        ]);

        $response->assertSessionHasErrors([
            'genres.0' => '選択したジャンルが存在しません。',
        ]);

        $this->assertDatabaseMissing('books', [
            'isbn' => '9784000000220',
        ]);
    }

    /**
     * No.30
     * ジャンルに配列以外の値を指定して書籍登録を行い、
     * バリデーションエラーとなり、書籍が登録されないこと。
     */
    public function test_book_registration_fails_when_genres_is_not_an_array(): void
    {
        $user = User::create([
            'name' => 'ジャンル形式テストユーザー',
            'email' => 'book-genre-format-error@example.com',
            'password' => 'password',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('books.store'), [
            'title' => 'ジャンル形式テスト書籍',
            'author' => 'ジャンル形式テスト著者',
            'isbn' => '9784000000237',
            'published_date' => '2025-01-01',
            'description' => 'テスト用の説明です。',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => 'invalid-genres',
        ]);

        $response->assertSessionHasErrors([
            'genres' => 'ジャンルの形式が正しくありません。',
        ]);

        $this->assertDatabaseMissing('books', [
            'isbn' => '9784000000237',
        ]);
    }

    /**
     * No.30
     * 1,000文字を超える説明文で書籍登録を行い、
     * バリデーションエラーとなり、書籍が登録されないこと。
     */
    public function test_book_registration_fails_when_description_exceeds_1000_characters(): void
    {
        $user = User::create([
            'name' => '説明文文字数テストユーザー',
            'email' => 'book-description-length-error@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '説明文文字数テストジャンル',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('books.store'), [
            'title' => '説明文文字数テスト書籍',
            'author' => '説明文文字数テスト著者',
            'isbn' => '9784000000244',
            'published_date' => '2025-01-01',
            'description' => str_repeat('あ', 1001),
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'description' => '説明は1000文字以内で入力してください。',
        ]);

        $this->assertDatabaseMissing('books', [
            'isbn' => '9784000000244',
        ]);
    }

    /**
     * No.30
     * 文字列以外の説明文で書籍登録を行い、
     * バリデーションエラーとなり、書籍が登録されないこと。
     */
    public function test_book_registration_fails_when_description_is_not_a_string(): void
    {
        $user = User::create([
            'name' => '説明文形式テストユーザー',
            'email' => 'book-description-format-error@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '説明文形式テストジャンル',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('books.store'), [
            'title' => '説明文形式テスト書籍',
            'author' => '説明文形式テスト著者',
            'isbn' => '9784000000251',
            'published_date' => '2025-01-01',
            'description' => 12345,
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'description' => '説明は文字列で入力してください。',
        ]);

        $this->assertDatabaseMissing('books', [
            'isbn' => '9784000000251',
        ]);
    }

    /**
     * No.30
     * 255文字を超えるタイトルで書籍登録を行い、
     * バリデーションエラーとなり、書籍が登録されないこと。
     */
    public function test_book_registration_fails_when_title_exceeds_255_characters(): void
    {
        $user = User::create([
            'name' => 'タイトル文字数テストユーザー',
            'email' => 'book-title-length-error@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => 'タイトル文字数テストジャンル',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('books.store'), [
            'title' => str_repeat('あ', 256),
            'author' => 'タイトル文字数テスト著者',
            'isbn' => '9784000000268',
            'published_date' => '2025-01-01',
            'description' => 'テスト用の説明です。',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'title' => 'タイトルは255文字以内で入力してください。',
        ]);

        $this->assertDatabaseMissing('books', [
            'isbn' => '9784000000268',
        ]);
    }

    /**
     * No.30
     * 255文字を超える著者名で書籍登録を行い、
     * バリデーションエラーとなり、書籍が登録されないこと。
     */
    public function test_book_registration_fails_when_author_exceeds_255_characters(): void
    {
        $user = User::create([
            'name' => '著者文字数テストユーザー',
            'email' => 'book-author-length-error@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '著者文字数テストジャンル',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('books.store'), [
            'title' => '著者文字数テスト書籍',
            'author' => str_repeat('あ', 256),
            'isbn' => '9784000000275',
            'published_date' => '2025-01-01',
            'description' => 'テスト用の説明です。',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'author' => '著者は255文字以内で入力してください。',
        ]);

        $this->assertDatabaseMissing('books', [
            'isbn' => '9784000000275',
        ]);
    }

    public function test_book_registration_fails_when_author_is_not_a_string(): void
    {
        $user = User::factory()->create([
            'email' => 'book-author-type-error@example.com',
        ]);

        $genre = Genre::create([
            'name' => '著者形式テストジャンル',
        ]);

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => '著者形式テスト書籍',
            'author' => ['著者A'],
            'isbn' => '9784000000282',
            'published_date' => '2025-01-01',
            'description' => 'テスト用の説明',
            'image_url' => null,
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'author' => '著者は文字列で入力してください。',
        ]);

        $this->assertDatabaseMissing('books', [
            'isbn' => '9784000000282',
        ]);
    }

    public function test_book_registration_fails_when_title_is_not_a_string(): void
    {
        $user = User::factory()->create([
            'email' => 'book-title-type-error@example.com',
        ]);

        $genre = Genre::create([
            'name' => 'タイトル形式テストジャンル',
        ]);

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => ['タイトルA'],
            'author' => 'テスト著者',
            'isbn' => '9784000000299',
            'published_date' => '2025-01-01',
            'description' => 'テスト用の説明',
            'image_url' => null,
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors([
            'title' => 'タイトルは文字列で入力してください。',
        ]);

        $this->assertDatabaseMissing('books', [
            'isbn' => '9784000000299',
        ]);
    }

    public function test_book_registration_fails_when_isbn_is_not_a_string(): void
    {
        $user = User::factory()->create([
            'email' => 'book-isbn-type-error@example.com',
        ]);

        $genre = Genre::create([
            'name' => 'ISBN形式テストジャンル',
        ]);

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'ISBN形式テスト書籍',
            'author' => 'テスト著者',
            'isbn' => ['9784000000305'],
            'published_date' => '2025-01-01',
            'description' => 'テスト用の説明',
            'image_url' => null,
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors('isbn');

        $this->assertDatabaseMissing('books', [
            'title' => 'ISBN形式テスト書籍',
        ]);
    }

    /**
     * 書籍の所有者が削除権限を持ち、書籍を削除し、
     * 一覧画面へリダイレクトされ、成功メッセージが表示されること。
     */
    public function test_book_owner_can_delete_book_successfully(): void
    {
        $user = User::create([
            'name' => '書籍削除テストユーザー',
            'email' => 'book-delete-success@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '削除テストジャンル',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '削除対象のタイトル',
            'author' => '削除対象の著者',
            'isbn' => '9784000000283',
            'published_date' => '2026-09-20',
            'description' => '削除対象の説明です。',
            'image_url' => 'https://example.com/delete.jpg',
        ]);

        $book->genres()->attach($genre->id);

        $this->actingAs($user);

        $response = $this->delete(route('books.destroy', $book));

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);

        $response->assertRedirect(route('books.index'));

        $response->assertSessionHas(
            'success',
            '書籍を削除しました。'
        );
    }

    /**
     * 書籍の所有者以外が削除を実行した場合、403エラーとなり、
     * 書籍が削除されないこと。
     */
    public function test_non_owner_cannot_delete_book(): void
    {
        $owner = User::create([
            'name' => '書籍所有者',
            'email' => 'book-delete-owner@example.com',
            'password' => 'password',
        ]);

        $otherUser = User::create([
            'name' => '別のユーザー',
            'email' => 'book-delete-other@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '削除テストジャンル',
        ]);

        $book = Book::create([
            'user_id' => $owner->id,
            'title' => '削除対象のタイトル',
            'author' => '削除対象の著者',
            'isbn' => '9784000000284',
            'published_date' => '2026-09-20',
            'description' => '削除対象の説明です。',
            'image_url' => 'https://example.com/delete.jpg',
        ]);

        $book->genres()->attach($genre->id);

        $this->actingAs($otherUser);

        $response = $this->delete(route('books.destroy', $book));

        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'user_id' => $owner->id,
            'title' => '削除対象のタイトル',
        ]);
    }

    /**
     * 未ログインユーザーが書籍の削除を試みた場合、
     * ログイン画面へリダイレクトされ、書籍が削除されないこと。
     */
    public function test_guest_cannot_delete_book(): void
    {
        $owner = User::create([
            'name' => '書籍所有者',
            'email' => 'book-delete-guest-owner@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '削除テストジャンル',
        ]);

        $book = Book::create([
            'user_id' => $owner->id,
            'title' => '削除対象のタイトル',
            'author' => '削除対象の著者',
            'isbn' => '9784000000285',
            'published_date' => '2026-09-20',
            'description' => '削除対象の説明です。',
            'image_url' => 'https://example.com/delete.jpg',
        ]);

        $book->genres()->attach($genre->id);

        $response = $this->delete(route('books.destroy', $book));

        $response->assertRedirect(route('login'));

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'user_id' => $owner->id,
            'title' => '削除対象のタイトル',
        ]);
    }

    /**
     * 存在しない書籍IDを指定して削除を試みた場合、
     * 404エラーになること。
     */
    public function test_cannot_delete_nonexistent_book(): void
    {
        $user = User::create([
            'name' => '削除テストユーザー',
            'email' => 'book-delete-nonexistent@example.com',
            'password' => 'password',
        ]);

        $this->actingAs($user);

        $response = $this->delete(route('books.destroy', 999999));

        $response->assertNotFound();
    }

    /**
     * No.26
     * 書籍一覧を表示し、1ページに10件の書籍が表示され、
     * 11件目の書籍が次ページに表示されること。
     */
    public function test_book_list_displays_ten_books_per_page(): void
    {
        $user = User::create([
            'name' => '書籍一覧ページネーションテストユーザー',
            'email' => 'book-list-pagination@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => 'ページネーションテストジャンル',
        ]);

        for ($i = 1; $i <= 11; $i++) {
            $book = Book::create([
                'user_id' => $user->id,
                'title' => "ページネーションテスト書籍{$i}",
                'author' => "テスト著者{$i}",
                'isbn' => sprintf('978400000%04d', $i),
                'published_date' => '2026-09-01',
                'description' => 'ページネーション確認用の説明です。',
                'image_url' => null,
            ]);

            $book->genres()->attach($genre->id);
        }

        $response = $this->get(route('books.index'));

        $response->assertOk()
            ->assertViewHas('books', function ($books) {
                return $books->count() === 10 && $books->total() === 11;
            });
    }

    /**
     * 書籍の所有者以外が編集画面にアクセスした場合、403エラーとなること。
     */
    public function test_non_owner_cannot_access_book_edit_page(): void
    {
        $owner = User::create([
            'name' => '編集対象書籍の所有者',
            'email' => 'book-edit-forbidden-owner@example.com',
            'password' => 'password',
        ]);

        $otherUser = User::create([
            'name' => '編集画面アクセス別ユーザー',
            'email' => 'book-edit-forbidden-other@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '編集権限テストジャンル',
        ]);

        $book = Book::create([
            'user_id' => $owner->id,
            'title' => '他ユーザー編集画面アクセス対象書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000933',
            'published_date' => '2026-09-20',
            'description' => '編集画面の権限確認用の説明です。',
            'image_url' => 'https://example.com/edit-forbidden.jpg',
        ]);

        $book->genres()->attach($genre->id);

        $response = $this->actingAs($otherUser)
            ->get(route('books.edit', $book));

        $response->assertForbidden();
    }

    /**
     * 書籍の所有者以外が更新を実行した場合、403エラーとなり、
     * 書籍情報が変更されないこと。
     */
    public function test_non_owner_cannot_update_book(): void
    {
        $owner = User::create([
            'name' => '更新対象書籍の所有者',
            'email' => 'book-update-forbidden-owner@example.com',
            'password' => 'password',
        ]);

        $otherUser = User::create([
            'name' => '更新処理別ユーザー',
            'email' => 'book-update-forbidden-other@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '更新権限テストジャンル',
        ]);

        $book = Book::create([
            'user_id' => $owner->id,
            'title' => '他ユーザー更新対象書籍',
            'author' => '更新前著者',
            'isbn' => '9784000000940',
            'published_date' => '2026-09-20',
            'description' => '更新前の説明です。',
            'image_url' => 'https://example.com/update-before.jpg',
        ]);

        $book->genres()->attach($genre->id);

        $response = $this->actingAs($otherUser)
            ->put(route('books.update', $book), [
                'title' => '不正に変更しようとしたタイトル',
                'author' => '不正に変更しようとした著者',
                'isbn' => '9784000000957',
                'published_date' => '2026-09-21',
                'description' => '不正に変更しようとした説明です。',
                'image_url' => 'https://example.com/update-after.jpg',
                'genres' => [$genre->id],
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'user_id' => $owner->id,
            'title' => '他ユーザー更新対象書籍',
            'author' => '更新前著者',
            'isbn' => '9784000000940',
            'published_date' => '2026-09-20',
            'description' => '更新前の説明です。',
            'image_url' => 'https://example.com/update-before.jpg',
        ]);

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
            'title' => '不正に変更しようとしたタイトル',
            'isbn' => '9784000000957',
        ]);
    }

    /**
     * No.31
     * 書籍の所有者が編集画面にアクセスし、
     * 書籍情報とジャンル選択欄が表示されること。
     */
    public function test_book_edit_screen_displays_edit_form(): void
    {
        $user = User::create([
            'name' => '書籍編集画面テストユーザー',
            'email' => 'book-edit-screen@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '編集画面テストジャンル',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '編集画面テスト書籍',
            'author' => '編集画面テスト著者',
            'isbn' => '9784000000919',
            'published_date' => '2026-09-10',
            'description' => '編集画面テスト用の説明です。',
            'image_url' => 'https://example.com/edit-screen.jpg',
        ]);

        $book->genres()->attach($genre->id);

        $response = $this->actingAs($user)->get(route('books.edit', $book));

        $response->assertOk()
            ->assertSee('編集画面テスト書籍')
            ->assertSee('編集画面テスト著者')
            ->assertSee('9784000000919')
            ->assertSee('2026-09-10')
            ->assertSee('編集画面テスト用の説明です。')
            ->assertSee('https://example.com/edit-screen.jpg')
            ->assertSee('name="title"', false)
            ->assertSee('name="author"', false)
            ->assertSee('name="isbn"', false)
            ->assertSee('name="published_date"', false)
            ->assertSee('name="description"', false)
            ->assertSee('name="image_url"', false)
            ->assertSee('name="genres[]"', false)
            ->assertSee('編集画面テストジャンル');
    }

    /**
     * No.30
     * 書籍登録時にタイトルを未入力にして送信し、
     * 登録画面へ戻り、入力値と日本語のバリデーションエラーが保持され、
     * 書籍が登録されないこと。
     */
    public function test_book_registration_error_returns_to_form_and_keeps_old_input(): void
    {
        $user = User::create([
            'name' => '書籍登録入力保持テストユーザー',
            'email' => 'book-registration-old-input@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '登録入力保持テストジャンル',
        ]);

        $response = $this->actingAs($user)
            ->from(route('books.create'))
            ->post(route('books.store'), [
                'title' => '',
                'author' => '入力保持テスト著者',
                'isbn' => '9784000000926',
                'published_date' => '2026-09-12',
                'description' => '入力保持テスト用の説明です。',
                'image_url' => null,
                'genres' => [$genre->id],
            ]);

        $response->assertRedirect(route('books.create'))
            // エラーバッグに日本語のメッセージが格納されていることを確認する。
            ->assertSessionHasErrors([
                'title' => 'タイトルは必須です。',
            ])
            // 入力した値（空欄・任意項目を含む）が登録フォームへ引き継がれることを確認する。
            ->assertSessionHasInput('title', '')
            ->assertSessionHasInput('author', '入力保持テスト著者')
            ->assertSessionHasInput('isbn', '9784000000926')
            ->assertSessionHasInput('published_date', '2026-09-12')
            ->assertSessionHasInput('description', '入力保持テスト用の説明です。')
            ->assertSessionHasInput('genres', [$genre->id]);

        // バリデーションエラー時に書籍が登録されないことを確認する。
        $this->assertDatabaseMissing('books', [
            'isbn' => '9784000000926',
        ]);
    }

    /**
     * No.32
     * 書籍更新時にタイトルを未入力にして送信し、
     * 編集画面へ戻り、入力値と日本語のバリデーションエラーが保持され、
     * 書籍情報が更新されないこと。
     */
    public function test_book_update_error_returns_to_edit_form_and_keeps_old_input(): void
    {
        $user = User::create([
            'name' => '書籍更新入力保持テストユーザー',
            'email' => 'book-update-old-input@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '更新入力保持テストジャンル',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '入力保持テスト更新前タイトル',
            'author' => '入力保持テスト更新前著者',
            'isbn' => '9784000000933',
            'published_date' => '2026-09-13',
            'description' => '更新前の説明です。',
            'image_url' => null,
        ]);

        $book->genres()->attach($genre->id);

        $response = $this->actingAs($user)
            ->from(route('books.edit', $book))
            ->put(route('books.update', $book), [
                'title' => '',
                'author' => '入力保持テスト更新後著者',
                'isbn' => '9784000000940',
                'published_date' => '2026-09-14',
                'description' => '入力保持テスト更新後の説明です。',
                'image_url' => null,
                'genres' => [$genre->id],
            ]);

        $response->assertRedirect(route('books.edit', $book))
            // エラーバッグに日本語のメッセージが格納されていることを確認する。
            ->assertSessionHasErrors([
                'title' => 'タイトルは必須です。',
            ])
            // 入力した値（空欄・任意項目を含む）が編集フォームへ引き継がれることを確認する。
            ->assertSessionHasInput('title', '')
            ->assertSessionHasInput('author', '入力保持テスト更新後著者')
            ->assertSessionHasInput('isbn', '9784000000940')
            ->assertSessionHasInput('published_date', '2026-09-14')
            ->assertSessionHasInput('description', '入力保持テスト更新後の説明です。')
            ->assertSessionHasInput('genres', [$genre->id]);

        // バリデーションエラー時に、書籍の既存情報が一切更新されないことを確認する。
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '入力保持テスト更新前タイトル',
            'author' => '入力保持テスト更新前著者',
            'isbn' => '9784000000933',
            'published_date' => '2026-09-13',
            'description' => '更新前の説明です。',
            'image_url' => null,
        ]);

        // ジャンルの関連付けも変更されていないことを確認する。
        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }

    /**
     * No.33
     * 書籍を削除し、書籍に紐づくジャンル・レビュー・お気に入り・レビューいいねも
     * 要件に従って削除されること。
     */
    public function test_book_deletion_removes_related_records(): void
    {
        $owner = User::create([
            'name' => '関連データ削除テスト所有者',
            'email' => 'book-delete-related-owner@example.com',
            'password' => 'password',
        ]);

        $reviewUser = User::create([
            'name' => '関連データ削除テストレビュー投稿者',
            'email' => 'book-delete-related-reviewer@example.com',
            'password' => 'password',
        ]);

        $likeUser = User::create([
            'name' => '関連データ削除テストいいねユーザー',
            'email' => 'book-delete-related-liker@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => '関連データ削除テストジャンル',
        ]);

        $book = Book::create([
            'user_id' => $owner->id,
            'title' => '関連データ削除対象書籍',
            'author' => '関連データ削除対象著者',
            'isbn' => '9784000000957',
            'published_date' => '2026-09-15',
            'description' => '関連データ削除確認用の説明です。',
            'image_url' => null,
        ]);

        $book->genres()->attach($genre->id);

        $review = Review::create([
            'user_id' => $reviewUser->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '削除対象書籍のレビューです。',
        ]);

        ReviewLike::create([
            'user_id' => $likeUser->id,
            'review_id' => $review->id,
        ]);

        Favorite::create([
            'user_id' => $reviewUser->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($owner)->delete(route('books.destroy', $book));

        $response->assertRedirect(route('books.index'))
            ->assertSessionHas('success', '書籍を削除しました。');

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
        $this->assertDatabaseMissing('book_genre', ['book_id' => $book->id]);
        $this->assertDatabaseMissing('reviews', ['book_id' => $book->id]);
        $this->assertDatabaseMissing('favorites', ['book_id' => $book->id]);
        $this->assertDatabaseMissing('review_likes', ['review_id' => $review->id]);
    }
}
