<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * No.39：ジャンル一覧にジャンル名と書籍件数が表示されること。
     * また、ジャンル詳細に関連書籍が表示され、1ページあたり10件で表示されること。
     */
    public function test_genre_list_and_detail_display_correctly(): void
    {
        $user = User::factory()->create();

        $genre = Genre::create([
            'name' => 'テストジャンル',
        ]);

        $bookIds = [];

        for ($i = 1; $i <= 11; $i++) {
            $book = Book::create([
                'user_id' => $user->id,
                'title' => "テスト書籍{$i}",
                'author' => 'テスト著者',
                'isbn' => str_pad((string) $i, 13, '0', STR_PAD_LEFT),
                'published_date' => '2026-01-01',
            ]);

            $bookIds[] = $book->id;
        }

        $genre->books()->attach($bookIds);

        // ジャンル一覧を確認
        $response = $this->actingAs($user)
            ->get(route('genres.index'));

        $response->assertStatus(200);
        $response->assertSee('テストジャンル');

        $response->assertViewHas('genres', function ($genres) {
            return $genres->first()->books_count === 11;
        });

        // ジャンル詳細を確認
        $response = $this->actingAs($user)
            ->get(route('genres.show', $genre));

        $response->assertStatus(200);
        $response->assertSee('テスト書籍1');
        $response->assertSee('テスト書籍10');
        $response->assertDontSee('テスト書籍11');

        $response->assertViewHas('books', function ($books) {
            return $books->count() === 10
                && $books->total() === 11;
        });
    }

    /**
     * No.40：有効なジャンル名で登録すると、ジャンルが保存され、
     * ジャンル一覧へ遷移し、成功メッセージが表示されること。
     */
    public function test_genre_can_be_created_successfully(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('genres.store'), [
                'name' => '新規ジャンル',
            ]);

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas(
            'success',
            'ジャンルを登録しました。'
        );

        $this->assertDatabaseHas('genres', [
            'name' => '新規ジャンル',
        ]);
    }

    /**
     * No.41：ジャンル登録・編集で必須・文字数・重複等の不正値を送信すると、
     * 保存されず、該当フォームへ戻り、入力値が保持され、
     * 日本語のバリデーションエラーメッセージが表示されること。
     */
    public function test_genre_creation_and_update_fail_with_invalid_values(): void
    {
        $user = User::factory()->create();

        $genre = Genre::create([
            'name' => '既存ジャンル',
        ]);

        $anotherGenre = Genre::create([
            'name' => '別のジャンル',
        ]);

        // 登録：ジャンル名が未入力の場合
        $response = $this->actingAs($user)
            ->from(route('genres.create'))
            ->post(route('genres.store'), [
                'name' => '',
            ]);

        $response->assertRedirect(route('genres.create'));
        $response->assertSessionHasErrors([
            'name' => 'ジャンル名は必須です。',
        ]);
        $response->assertSessionHas('_old_input.name', '');

        $this->assertDatabaseMissing('genres', [
            'name' => '',
        ]);

        // 登録：ジャンル名が255文字を超える場合
        $invalidName = str_repeat('あ', 256);

        $response = $this->actingAs($user)
            ->from(route('genres.create'))
            ->post(route('genres.store'), [
                'name' => $invalidName,
            ]);

        $response->assertRedirect(route('genres.create'));
        $response->assertSessionHasErrors([
            'name' => 'ジャンル名は255文字以内で入力してください。',
        ]);
        $response->assertSessionHas('_old_input.name', $invalidName);

        $this->assertDatabaseMissing('genres', [
            'name' => $invalidName,
        ]);

        // 登録：既に登録されているジャンル名を指定した場合
        $response = $this->actingAs($user)
            ->from(route('genres.create'))
            ->post(route('genres.store'), [
                'name' => '既存ジャンル',
            ]);

        $response->assertRedirect(route('genres.create'));
        $response->assertSessionHasErrors([
            'name' => 'このジャンル名はすでに登録されています。',
        ]);
        $response->assertSessionHas('_old_input.name', '既存ジャンル');

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '既存ジャンル',
        ]);

        // 編集：ジャンル名が未入力の場合
        $response = $this->actingAs($user)
            ->from(route('genres.edit', $genre))
            ->put(route('genres.update', $genre), [
                'name' => '',
            ]);

        $response->assertRedirect(route('genres.edit', $genre));
        $response->assertSessionHasErrors([
            'name' => 'ジャンル名は必須です。',
        ]);
        $response->assertSessionHas('_old_input.name', '');

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '既存ジャンル',
        ]);

        // 編集：ジャンル名が255文字を超える場合
        $response = $this->actingAs($user)
            ->from(route('genres.edit', $genre))
            ->put(route('genres.update', $genre), [
                'name' => $invalidName,
            ]);

        $response->assertRedirect(route('genres.edit', $genre));
        $response->assertSessionHasErrors([
            'name' => 'ジャンル名は255文字以内で入力してください。',
        ]);
        $response->assertSessionHas('_old_input.name', $invalidName);

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '既存ジャンル',
        ]);

        // 編集：他のジャンルと重複する名前を指定した場合
        $response = $this->actingAs($user)
            ->from(route('genres.edit', $genre))
            ->put(route('genres.update', $genre), [
                'name' => $anotherGenre->name,
            ]);

        $response->assertRedirect(route('genres.edit', $genre));
        $response->assertSessionHasErrors([
            'name' => 'このジャンル名はすでに登録されています。',
        ]);
        $response->assertSessionHas('_old_input.name', '別のジャンル');

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '既存ジャンル',
        ]);
    }

    /**
     * No.42：ログイン済みユーザーがジャンル名を有効な値へ更新すると、
     * 更新内容が保存され、ジャンル一覧へ遷移し、
     * 成功メッセージが表示されること。
     */
    public function test_genre_can_be_updated_successfully(): void
    {
        $user = User::factory()->create();

        $genre = Genre::create([
            'name' => '更新前ジャンル',
        ]);

        $response = $this->actingAs($user)
            ->put(route('genres.update', $genre), [
                'name' => '更新後ジャンル',
            ]);

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas(
            'success',
            'ジャンルを更新しました。'
        );

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '更新後ジャンル',
        ]);

        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
            'name' => '更新前ジャンル',
        ]);
    }

    /**
     * No.43：書籍が紐づいていないジャンルを削除すると、
     * ジャンルが削除され、ジャンル一覧へ遷移し、
     * 成功メッセージが表示されること。
     */
    public function test_genre_can_be_deleted_successfully(): void
    {
        $user = User::factory()->create();

        $genre = Genre::create([
            'name' => '削除対象ジャンル',
        ]);

        $response = $this->actingAs($user)
            ->delete(route('genres.destroy', $genre));

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas(
            'success',
            'ジャンルを削除しました。'
        );

        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
        ]);
    }

    /**
     * No.44：書籍が1件以上紐づくジャンルを削除すると、
     * ジャンルが削除されず、エラーメッセージが表示されること。
     */
    public function test_genre_with_books_cannot_be_deleted(): void
    {
        $user = User::factory()->create();

        $genre = Genre::create([
            'name' => '削除制限ジャンル',
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '紐づく書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
        ]);

        $genre->books()->attach($book->id);

        $response = $this->actingAs($user)
            ->from(route('genres.index'))
            ->delete(route('genres.destroy', $genre));

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas(
            'error',
            '書籍が登録されているジャンルは削除できません。'
        );

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '削除制限ジャンル',
        ]);
    }
}
