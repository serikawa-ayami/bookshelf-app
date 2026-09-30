<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Favorite;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_book_list(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_guest_can_access_book_detail(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000001',
            'published_date' => '2026-01-01',
            'description' => 'テスト用の書籍です。',
            'image_url' => null,
        ]);

        $genre = Genre::create([
            'name' => 'テストジャンル',
        ]);

        $book->genres()->attach($genre->id);

        $response = $this->get('/books/'.$book->id);

        $response->assertStatus(200);
    }

    public function test_guest_can_access_ranking(): void
    {
        $response = $this->get('/ranking');

        $response->assertStatus(200);
    }

    public function test_guest_is_redirected_to_login_from_favorites(): void
    {
        $response = $this->get('/favorites');

        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_genres(): void
    {
        $response = $this->get('/genres');

        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_book_create(): void
    {
        $response = $this->get('/books/create');

        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_genre_detail(): void
    {
        $genre = Genre::create([
            'name' => 'テストジャンル',
        ]);

        $response = $this->get('/genres/'.$genre->id);

        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_genre_create(): void
    {
        $response = $this->get('/genres/create');

        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_genre_edit(): void
    {
        $genre = Genre::create([
            'name' => 'テストジャンル',
        ]);

        $response = $this->get('/genres/'.$genre->id.'/edit');

        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_genre_delete(): void
    {
        $genre = Genre::create([
            'name' => 'テストジャンル',
        ]);

        $response = $this->delete('/genres/'.$genre->id);

        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_book_edit(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'book-edit@example.com',
            'password' => 'password',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000002',
            'published_date' => '2026-01-01',
            'description' => 'テスト用の書籍です。',
            'image_url' => null,
        ]);

        $response = $this->get('/books/'.$book->id.'/edit');

        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_book_delete(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'book-delete@example.com',
            'password' => 'password',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000003',
            'published_date' => '2026-01-01',
            'description' => 'テスト用の書籍です。',
            'image_url' => null,
        ]);

        $response = $this->delete('/books/'.$book->id);

        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_review_edit(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'review-edit@example.com',
            'password' => 'password',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000004',
            'published_date' => '2026-01-01',
            'description' => 'テスト用の書籍です。',
            'image_url' => null,
        ]);

        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'テストレビュー',
        ]);

        $response = $this->get('/reviews/'.$review->id.'/edit');

        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_review_delete(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'review-delete@example.com',
            'password' => 'password',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000005',
            'published_date' => '2026-01-01',
            'description' => 'テスト用の書籍です。',
            'image_url' => null,
        ]);

        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'テストレビュー',
        ]);

        $response = $this->delete('/reviews/'.$review->id);

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_is_redirected_from_login_to_books(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'login-redirect@example.com',
            'password' => 'password',
        ]);

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/');
    }

    public function test_authenticated_user_is_redirected_from_register_to_books(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'register-redirect@example.com',
            'password' => 'password',
        ]);

        $response = $this->actingAs($user)->get('/register');

        $response->assertRedirect('/');
    }

    public function test_book_list_is_paginated_by_10_items(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'pagination-books@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => 'テストジャンル',
        ]);

        for ($i = 1; $i <= 11; $i++) {
            $book = Book::create([
                'user_id' => $user->id,
                'title' => 'テスト書籍'.$i,
                'author' => 'テスト著者'.$i,
                'isbn' => '97840000000'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'published_date' => '2026-01-01',
                'description' => null,
                'image_url' => null,
            ]);

            $book->genres()->attach($genre->id);
        }

        $response = $this->get('/books');

        $response->assertStatus(200);
        $response->assertViewHas('books', function ($books) {
            return $books->count() === 10
                && $books->total() === 11
                && $books->currentPage() === 1
                && $books->lastPage() === 2;
        });

        $response = $this->get('/books?page=2');

        $response->assertStatus(200);
        $response->assertViewHas('books', function ($books) {
            return $books->count() === 1
                && $books->currentPage() === 2;
        });
    }

    public function test_favorites_are_paginated_by_10_items(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'pagination-favorites@example.com',
            'password' => 'password',
        ]);

        for ($i = 1; $i <= 11; $i++) {
            $book = Book::create([
                'user_id' => $user->id,
                'title' => 'お気に入り書籍'.$i,
                'author' => 'テスト著者'.$i,
                'isbn' => '97840000001'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'published_date' => '2026-01-01',
                'description' => null,
                'image_url' => null,
            ]);

            Favorite::create([
                'user_id' => $user->id,
                'book_id' => $book->id,
            ]);
        }

        $response = $this->actingAs($user)->get('/favorites');

        $response->assertStatus(200);
        $response->assertViewHas('books', function ($books) {
            return $books->count() === 10
                && $books->total() === 11
                && $books->currentPage() === 1
                && $books->lastPage() === 2;
        });

        $response = $this->actingAs($user)->get('/favorites?page=2');

        $response->assertStatus(200);
        $response->assertViewHas('books', function ($books) {
            return $books->count() === 1
                && $books->currentPage() === 2;
        });
    }

    public function test_genre_detail_is_paginated_by_10_books(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'pagination-genre@example.com',
            'password' => 'password',
        ]);

        $genre = Genre::create([
            'name' => 'ページネーションジャンル',
        ]);

        for ($i = 1; $i <= 11; $i++) {
            $book = Book::create([
                'user_id' => $user->id,
                'title' => 'ジャンル書籍'.$i,
                'author' => 'テスト著者'.$i,
                'isbn' => '97840000002'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'published_date' => '2026-01-01',
                'description' => null,
                'image_url' => null,
            ]);

            $book->genres()->attach($genre->id);
        }

        $response = $this->actingAs($user)->get('/genres/'.$genre->id);

        $response->assertStatus(200);
        $response->assertViewHas('books', function ($books) {
            return $books->count() === 10
                && $books->total() === 11
                && $books->currentPage() === 1
                && $books->lastPage() === 2;
        });

        $response = $this->actingAs($user)->get(
            '/genres/'.$genre->id.'?page=2'
        );

        $response->assertStatus(200);
        $response->assertViewHas('books', function ($books) {
            return $books->count() === 1
                && $books->currentPage() === 2;
        });
    }
}
