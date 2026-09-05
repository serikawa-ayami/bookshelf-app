<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    public function run(): void
    {
        $reviewLikes = [
            // 吾輩は猫である
            [
                'review_user_email' => 'yamada@example.com',
                'book_isbn' => '9784101010014',
                'like_user_emails' => [
                    'suzuki@example.com',
                    'tanaka@example.com',
                ],
            ],
            [
                'review_user_email' => 'suzuki@example.com',
                'book_isbn' => '9784101010014',
                'like_user_emails' => [
                    'yamada@example.com',
                    'sato@example.com',
                ],
            ],
            [
                'review_user_email' => 'tanaka@example.com',
                'book_isbn' => '9784101010014',
                'like_user_emails' => [
                    'yamada@example.com',
                    'takahashi@example.com',
                ],
            ],

            // 人を動かす
            [
                'review_user_email' => 'suzuki@example.com',
                'book_isbn' => '9784422100524',
                'like_user_emails' => [
                    'tanaka@example.com',
                    'sato@example.com',
                ],
            ],
            [
                'review_user_email' => 'tanaka@example.com',
                'book_isbn' => '9784422100524',
                'like_user_emails' => [
                    'suzuki@example.com',
                    'takahashi@example.com',
                ],
            ],
            [
                'review_user_email' => 'sato@example.com',
                'book_isbn' => '9784422100524',
                'like_user_emails' => [
                    'yamada@example.com',
                    'tanaka@example.com',
                ],
            ],

            // リーダブルコード
            [
                'review_user_email' => 'tanaka@example.com',
                'book_isbn' => '9784873115658',
                'like_user_emails' => [
                    'sato@example.com',
                    'takahashi@example.com',
                ],
            ],
            [
                'review_user_email' => 'sato@example.com',
                'book_isbn' => '9784873115658',
                'like_user_emails' => [
                    'yamada@example.com',
                    'suzuki@example.com',
                ],
            ],
            [
                'review_user_email' => 'takahashi@example.com',
                'book_isbn' => '9784873115658',
                'like_user_emails' => [
                    'yamada@example.com',
                    'tanaka@example.com',
                ],
            ],

            // 7つの習慣
            [
                'review_user_email' => 'sato@example.com',
                'book_isbn' => '9784863940246',
                'like_user_emails' => [
                    'suzuki@example.com',
                    'takahashi@example.com',
                ],
            ],
            [
                'review_user_email' => 'takahashi@example.com',
                'book_isbn' => '9784863940246',
                'like_user_emails' => [
                    'tanaka@example.com',
                    'sato@example.com',
                ],
            ],
            [
                'review_user_email' => 'yamada@example.com',
                'book_isbn' => '9784863940246',
                'like_user_emails' => [
                    'suzuki@example.com',
                    'takahashi@example.com',
                ],
            ],

            // 坊っちゃん
            [
                'review_user_email' => 'takahashi@example.com',
                'book_isbn' => '9784101010021',
                'like_user_emails' => [
                    'yamada@example.com',
                    'sato@example.com',
                ],
            ],
            [
                'review_user_email' => 'yamada@example.com',
                'book_isbn' => '9784101010021',
                'like_user_emails' => [
                    'tanaka@example.com',
                    'takahashi@example.com',
                ],
            ],

            // サピエンス全史
            [
                'review_user_email' => 'yamada@example.com',
                'book_isbn' => '9784309226712',
                'like_user_emails' => [
                    'suzuki@example.com',
                    'sato@example.com',
                ],
            ],
            [
                'review_user_email' => 'suzuki@example.com',
                'book_isbn' => '9784309226712',
                'like_user_emails' => [
                    'tanaka@example.com',
                    'takahashi@example.com',
                ],
            ],
            [
                'review_user_email' => 'takahashi@example.com',
                'book_isbn' => '9784309226712',
                'like_user_emails' => [
                    'yamada@example.com',
                    'sato@example.com',
                ],
            ],

            // Clean Code
            [
                'review_user_email' => 'suzuki@example.com',
                'book_isbn' => '9784048930598',
                'like_user_emails' => [
                    'yamada@example.com',
                    'tanaka@example.com',
                ],
            ],
            [
                'review_user_email' => 'tanaka@example.com',
                'book_isbn' => '9784048930598',
                'like_user_emails' => [
                    'suzuki@example.com',
                    'sato@example.com',
                ],
            ],
            [
                'review_user_email' => 'takahashi@example.com',
                'book_isbn' => '9784048930598',
                'like_user_emails' => [
                    'yamada@example.com',
                    'suzuki@example.com',
                ],
            ],

            // 嫌われる勇気
            [
                'review_user_email' => 'sato@example.com',
                'book_isbn' => '9784478025819',
                'like_user_emails' => [
                    'tanaka@example.com',
                    'takahashi@example.com',
                ],
            ],
            [
                'review_user_email' => 'takahashi@example.com',
                'book_isbn' => '9784478025819',
                'like_user_emails' => [
                    'yamada@example.com',
                    'suzuki@example.com',
                ],
            ],
            [
                'review_user_email' => 'yamada@example.com',
                'book_isbn' => '9784478025819',
                'like_user_emails' => [
                    'sato@example.com',
                    'takahashi@example.com',
                ],
            ],

            // 火花
            [
                'review_user_email' => 'yamada@example.com',
                'book_isbn' => '9784163902302',
                'like_user_emails' => [
                    'suzuki@example.com',
                    'tanaka@example.com',
                ],
            ],
            [
                'review_user_email' => 'suzuki@example.com',
                'book_isbn' => '9784163902302',
                'like_user_emails' => [
                    'yamada@example.com',
                    'sato@example.com',
                ],
            ],
            [
                'review_user_email' => 'sato@example.com',
                'book_isbn' => '9784163902302',
                'like_user_emails' => [
                    'tanaka@example.com',
                    'takahashi@example.com',
                ],
            ],

            // FACTFULNESS
            [
                'review_user_email' => 'tanaka@example.com',
                'book_isbn' => '9784822289607',
                'like_user_emails' => [
                    'yamada@example.com',
                    'suzuki@example.com',
                ],
            ],
            [
                'review_user_email' => 'sato@example.com',
                'book_isbn' => '9784822289607',
                'like_user_emails' => [
                    'yamada@example.com',
                    'takahashi@example.com',
                ],
            ],

            // コンテナ物語
            [
                'review_user_email' => 'takahashi@example.com',
                'book_isbn' => '9784822251468',
                'like_user_emails' => [
                    'suzuki@example.com',
                    'tanaka@example.com',
                ],
            ],
            [
                'review_user_email' => 'yamada@example.com',
                'book_isbn' => '9784822251468',
                'like_user_emails' => [
                    'sato@example.com',
                    'takahashi@example.com',
                ],
            ],
            [
                'review_user_email' => 'suzuki@example.com',
                'book_isbn' => '9784822251468',
                'like_user_emails' => [
                    'tanaka@example.com',
                    'sato@example.com',
                ],
            ],
            [
                'review_user_email' => 'tanaka@example.com',
                'book_isbn' => '9784822251468',
                'like_user_emails' => [
                    'yamada@example.com',
                    'takahashi@example.com',
                ],
            ],
        ];

        foreach ($reviewLikes as $reviewLikeData) {
            $reviewUser = User::where(
                'email',
                $reviewLikeData['review_user_email']
            )->first();

            $book = Book::where(
                'isbn',
                $reviewLikeData['book_isbn']
            )->first();

            $review = Review::where('user_id', $reviewUser->id)
                ->where('book_id', $book->id)
                ->first();

            $userIds = User::whereIn(
                'email',
                $reviewLikeData['like_user_emails']
            )->pluck('id')->toArray();

            $review->likedByUsers()->syncWithoutDetaching($userIds);
        }
    }
}
