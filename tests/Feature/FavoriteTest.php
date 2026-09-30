<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * No.45：ログイン済みユーザーが書籍のお気に入りを追加し、
     * 再操作で解除することで、トグルが正しく動作し、
     * 追加・解除それぞれの成功メッセージが表示されること。
     */
    public function test_favorite_can_be_added_and_removed_by_toggle(): void
    {
        $user = User::factory()->create();
        $book = $this->createBook($user);

        // お気に入りを追加する
        $response = $this->actingAs($user)
            ->post(route('favorites.toggle', $book));

        $response->assertRedirect();
        $response->assertSessionHas(
            'success',
            'お気に入りに追加しました。'
        );

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        // お気に入りを再操作して解除する
        $response = $this->actingAs($user)
            ->post(route('favorites.toggle', $book));

        $response->assertRedirect();
        $response->assertSessionHas(
            'success',
            'お気に入りから削除しました。'
        );

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    /**
     * No.46：ログイン済みユーザーがお気に入り一覧にアクセスすると、
     * 自分のお気に入りのみが表示され、1ページあたり10件で表示され、
     * 書籍タイトルから詳細画面へ遷移できること。
     */
    public function test_favorite_list_displays_only_own_favorites_with_pagination(): void
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();

        // 自分のお気に入り書籍を11件作成する
        $favoriteBooks = [];

        for ($i = 1; $i <= 11; $i++) {
            $book = $this->createBook(
                $user,
                "お気に入り書籍{$i}",
                $i
            );

            $favoriteBooks[] = $book;

            Favorite::create([
                'user_id' => $user->id,
                'book_id' => $book->id,
            ]);
        }

        // 他のユーザーのお気に入り書籍を作成する
        $otherBook = $this->createBook(
            $anotherUser,
            '他ユーザーのお気に入り書籍',
            100
        );

        Favorite::create([
            'user_id' => $anotherUser->id,
            'book_id' => $otherBook->id,
        ]);

        // お気に入り一覧を表示する
        $response = $this->actingAs($user)
            ->get(route('favorites.index'));

        $response->assertStatus(200);

        // 自分のお気に入り書籍が表示されること
        $response->assertSee('お気に入り書籍1');

        // 他のユーザーのお気に入り書籍が表示されないこと
        $response->assertDontSee('他ユーザーのお気に入り書籍');

        // 1ページあたり10件で、全11件が取得対象であること
        $response->assertViewHas('books', function ($books) {
            return $books->count() === 10
                && $books->total() === 11;
        });

        // 書籍タイトルから詳細画面へ遷移するリンクが表示されること
        $response->assertSee(
            route('books.show', $favoriteBooks[0]),
            false
        );

        // 2ページ目に残りの1件が表示されること
        $response = $this->actingAs($user)
            ->get(route('favorites.index', ['page' => 2]));

        $response->assertStatus(200);
        $response->assertSee('お気に入り書籍11');
        $response->assertDontSee('他ユーザーのお気に入り書籍');

        $response->assertViewHas('books', function ($books) {
            return $books->count() === 1
                && $books->total() === 11;
        });
    }

    /**
     * No.47：同一ユーザー・同一書籍に対してお気に入り操作を複数回行い、
     * お気に入りが重複登録されず、追加・解除・再追加の各段階で
     * トグル状態が正しく維持されること。
     */
    public function test_repeated_favorite_toggle_does_not_create_duplicates(): void
    {
        $user = User::factory()->create();
        $book = $this->createBook($user);

        // 1回目：お気に入りを追加する
        $this->actingAs($user)
            ->post(route('favorites.toggle', $book))
            ->assertSessionHas(
                'success',
                'お気に入りに追加しました。'
            );

        $this->assertSame(
            1,
            Favorite::where('user_id', $user->id)
                ->where('book_id', $book->id)
                ->count()
        );

        // 2回目：お気に入りを解除する
        $this->actingAs($user)
            ->post(route('favorites.toggle', $book))
            ->assertSessionHas(
                'success',
                'お気に入りから削除しました。'
            );

        $this->assertSame(
            0,
            Favorite::where('user_id', $user->id)
                ->where('book_id', $book->id)
                ->count()
        );

        // 3回目：再度お気に入りを追加する
        $this->actingAs($user)
            ->post(route('favorites.toggle', $book))
            ->assertSessionHas(
                'success',
                'お気に入りに追加しました。'
            );

        $this->assertSame(
            1,
            Favorite::where('user_id', $user->id)
                ->where('book_id', $book->id)
                ->count()
        );

        // 4回目：再度解除し、お気に入りが残らないこと
        $this->actingAs($user)
            ->post(route('favorites.toggle', $book))
            ->assertSessionHas(
                'success',
                'お気に入りから削除しました。'
            );

        $this->assertSame(
            0,
            Favorite::where('user_id', $user->id)
                ->where('book_id', $book->id)
                ->count()
        );
    }

    /**
     * テスト用の書籍を作成する。
     */
    private function createBook(
        User $user,
        string $title = 'テスト書籍',
        int $number = 1
    ): Book {
        return Book::create([
            'user_id' => $user->id,
            'title' => $title,
            'author' => 'テスト著者',
            'isbn' => str_pad(
                (string) $number,
                13,
                '0',
                STR_PAD_LEFT
            ),
            'published_date' => '2026-01-01',
        ]);
    }
}
