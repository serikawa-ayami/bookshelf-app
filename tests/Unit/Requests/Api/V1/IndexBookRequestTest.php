<?php

namespace Tests\Unit\Requests\Api\V1;

use App\Http\Requests\Api\V1\IndexBookRequest;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * No.54：AP01 書籍一覧APIのバリデーションテスト
 */
class IndexBookRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * バリデーションを実行する
     */
    private function validate(array $data): bool
    {
        $request = new IndexBookRequest;

        return Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        )->passes();
    }

    /**
     * No.54：正常なクエリパラメータを指定すると、バリデーションを通過すること。
     */
    public function test_valid_query_parameters_pass_validation(): void
    {
        $genre = Genre::create([
            'name' => 'テストジャンル',
        ]);

        $this->assertTrue($this->validate([
            'keyword' => 'テスト書籍',
            'genre_id' => $genre->id,
            'page' => 1,
            'per_page' => 10,
        ]));
    }

    /**
     * No.54：keywordにnullを指定すると、バリデーションを通過すること。
     */
    public function test_keyword_can_be_null(): void
    {
        $this->assertTrue($this->validate([
            'keyword' => null,
        ]));
    }

    /**
     * No.54：keywordに256文字を指定すると、バリデーションエラーになること。
     */
    public function test_keyword_over_255_characters_is_invalid(): void
    {
        $this->assertFalse($this->validate([
            'keyword' => str_repeat('あ', 256),
        ]));
    }

    /**
     * No.54：keywordに文字列以外を指定すると、バリデーションエラーになること。
     */
    public function test_keyword_must_be_string(): void
    {
        $this->assertFalse($this->validate([
            'keyword' => ['テスト'],
        ]));
    }

    /**
     * No.54：genre_idに整数以外を指定すると、バリデーションエラーになること。
     */
    public function test_genre_id_must_be_integer(): void
    {
        $this->assertFalse($this->validate([
            'genre_id' => 'abc',
        ]));
    }

    /**
     * No.54：genre_idに存在しないIDを指定すると、バリデーションエラーになること。
     */
    public function test_genre_id_must_exist(): void
    {
        $this->assertFalse($this->validate([
            'genre_id' => 99999,
        ]));
    }

    /**
     * No.54：pageに整数以外を指定すると、バリデーションエラーになること。
     */
    public function test_page_must_be_integer(): void
    {
        $this->assertFalse($this->validate([
            'page' => 'abc',
        ]));
    }

    /**
     * No.54：pageに1未満の値を指定すると、バリデーションエラーになること。
     */
    public function test_page_must_be_at_least_one(): void
    {
        $this->assertFalse($this->validate([
            'page' => 0,
        ]));
    }

    /**
     * No.54：per_pageに整数以外を指定すると、バリデーションエラーになること。
     */
    public function test_per_page_must_be_integer(): void
    {
        $this->assertFalse($this->validate([
            'per_page' => 'abc',
        ]));
    }

    /**
     * No.54：per_pageに1未満の値を指定すると、バリデーションエラーになること。
     */
    public function test_per_page_must_be_at_least_one(): void
    {
        $this->assertFalse($this->validate([
            'per_page' => 0,
        ]));
    }

    /**
     * No.54：per_pageに101を指定すると、バリデーションエラーになること。
     */
    public function test_per_page_must_not_exceed_100(): void
    {
        $this->assertFalse($this->validate([
            'per_page' => 101,
        ]));
    }

    /**
     * No.54：per_pageに上限値の100を指定すると、バリデーションを通過すること。
     */
    public function test_per_page_at_100_is_valid(): void
    {
        $this->assertTrue($this->validate([
            'per_page' => 100,
        ]));
    }
}
