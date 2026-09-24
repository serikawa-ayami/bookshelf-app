<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ReviewValidationTest extends TestCase
{
    private function validateStore(array $data): bool
    {
        $request = new StoreReviewRequest();

        return Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        )->passes();
    }

    private function validateUpdate(array $data): bool
    {
        $request = new UpdateReviewRequest();

        return Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        )->passes();
    }

    public function test_valid_review_data_passes_store_validation(): void
    {
        $this->assertTrue(
            $this->validateStore([
                'rating' => 3,
                'comment' => 'テストレビューです。',
            ])
        );
    }

    public function test_valid_review_data_passes_update_validation(): void
    {
        $this->assertTrue(
            $this->validateUpdate([
                'rating' => 3,
                'comment' => '更新後のレビューです。',
            ])
        );
    }

    public function test_rating_1_is_valid_for_store(): void
    {
        $this->assertTrue(
            $this->validateStore([
                'rating' => 1,
                'comment' => 'テストレビューです。',
            ])
        );
    }

    public function test_rating_5_is_valid_for_store(): void
    {
        $this->assertTrue(
            $this->validateStore([
                'rating' => 5,
                'comment' => 'テストレビューです。',
            ])
        );
    }

    public function test_rating_0_is_invalid_for_store(): void
    {
        $this->assertFalse(
            $this->validateStore([
                'rating' => 0,
                'comment' => 'テストレビューです。',
            ])
        );
    }

    public function test_rating_6_is_invalid_for_store(): void
    {
        $this->assertFalse(
            $this->validateStore([
                'rating' => 6,
                'comment' => 'テストレビューです。',
            ])
        );
    }

    public function test_rating_is_required_for_store(): void
    {
        $this->assertFalse(
            $this->validateStore([
                'rating' => null,
                'comment' => 'テストレビューです。',
            ])
        );
    }

    public function test_rating_must_be_integer_for_store(): void
    {
        $this->assertFalse(
            $this->validateStore([
                'rating' => '3.5',
                'comment' => 'テストレビューです。',
            ])
        );
    }

    public function test_comment_at_255_characters_is_valid_for_store(): void
    {
        $this->assertTrue(
            $this->validateStore([
                'rating' => 3,
                'comment' => str_repeat('あ', 255),
            ])
        );
    }

    public function test_comment_over_255_characters_is_invalid_for_store(): void
    {
        $this->assertFalse(
            $this->validateStore([
                'rating' => 3,
                'comment' => str_repeat('あ', 256),
            ])
        );
    }

    public function test_comment_can_be_null_for_store(): void
    {
        $this->assertTrue(
            $this->validateStore([
                'rating' => 3,
                'comment' => null,
            ])
        );
    }

    public function test_comment_must_be_string_for_store(): void
    {
        $this->assertFalse(
            $this->validateStore([
                'rating' => 3,
                'comment' => 12345,
            ])
        );
    }

    public function test_rating_1_is_valid_for_update(): void
    {
        $this->assertTrue(
            $this->validateUpdate([
                'rating' => 1,
                'comment' => 'テストレビューです。',
            ])
        );
    }

    public function test_rating_5_is_valid_for_update(): void
    {
        $this->assertTrue(
            $this->validateUpdate([
                'rating' => 5,
                'comment' => 'テストレビューです。',
            ])
        );
    }

    public function test_rating_0_is_invalid_for_update(): void
    {
        $this->assertFalse(
            $this->validateUpdate([
                'rating' => 0,
                'comment' => 'テストレビューです。',
            ])
        );
    }

    public function test_rating_6_is_invalid_for_update(): void
    {
        $this->assertFalse(
            $this->validateUpdate([
                'rating' => 6,
                'comment' => 'テストレビューです。',
            ])
        );
    }

    public function test_rating_is_required_for_update(): void
    {
        $this->assertFalse(
            $this->validateUpdate([
                'rating' => null,
                'comment' => 'テストレビューです。',
            ])
        );
    }

    public function test_rating_must_be_integer_for_update(): void
    {
        $this->assertFalse(
            $this->validateUpdate([
                'rating' => '3.5',
                'comment' => 'テストレビューです。',
            ])
        );
    }

    public function test_comment_at_255_characters_is_valid_for_update(): void
    {
        $this->assertTrue(
            $this->validateUpdate([
                'rating' => 3,
                'comment' => str_repeat('あ', 255),
            ])
        );
    }

    public function test_comment_over_255_characters_is_invalid_for_update(): void
    {
        $this->assertFalse(
            $this->validateUpdate([
                'rating' => 3,
                'comment' => str_repeat('あ', 256),
            ])
        );
    }

    public function test_comment_can_be_null_for_update(): void
    {
        $this->assertTrue(
            $this->validateUpdate([
                'rating' => 3,
                'comment' => null,
            ])
        );
    }

    public function test_comment_must_be_string_for_update(): void
    {
        $this->assertFalse(
            $this->validateUpdate([
                'rating' => 3,
                'comment' => 12345,
            ])
        );
    }
}