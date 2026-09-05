<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $reviews = [
            // 吾輩は猫である
            [
                'user_email' => 'yamada@example.com',
                'book_isbn' => '9784101010014',
                'rating' => 5,
                'comment' => '猫の視点から描かれる人間社会が面白く、最後まで楽しく読めました。',
            ],
            [
                'user_email' => 'suzuki@example.com',
                'book_isbn' => '9784101010014',
                'rating' => 4,
                'comment' => '独特の表現が印象的で、昔の作品ですが新鮮な気持ちで読めました。',
            ],
            [
                'user_email' => 'tanaka@example.com',
                'book_isbn' => '9784101010014',
                'rating' => 3,
                'comment' => '少し難しい部分もありましたが、登場人物の描写が興味深かったです。',
            ],

            // 人を動かす
            [
                'user_email' => 'suzuki@example.com',
                'book_isbn' => '9784422100524',
                'rating' => 5,
                'comment' => '人との関わり方について具体的に考えるきっかけになりました。',
            ],
            [
                'user_email' => 'tanaka@example.com',
                'book_isbn' => '9784422100524',
                'rating' => 4,
                'comment' => '仕事や日常生活でも実践できそうな考え方が多く参考になりました。',
            ],
            [
                'user_email' => 'sato@example.com',
                'book_isbn' => '9784422100524',
                'rating' => 5,
                'comment' => '読みやすく、人間関係について改めて考えさせられる内容でした。',
            ],

            // リーダブルコード
            [
                'user_email' => 'tanaka@example.com',
                'book_isbn' => '9784873115658',
                'rating' => 5,
                'comment' => '読みやすいコードを書くための考え方が具体的で、とても勉強になりました。',
            ],
            [
                'user_email' => 'sato@example.com',
                'book_isbn' => '9784873115658',
                'rating' => 4,
                'comment' => '普段何となく書いていたコードを見直すきっかけになりました。',
            ],
            [
                'user_email' => 'takahashi@example.com',
                'book_isbn' => '9784873115658',
                'rating' => 5,
                'comment' => '実例が分かりやすく、プログラミング初心者にも参考になる内容でした。',
            ],

            // 7つの習慣
            [
                'user_email' => 'sato@example.com',
                'book_isbn' => '9784863940246',
                'rating' => 5,
                'comment' => '日々の行動を見直すきっかけになり、仕事にも活かせそうです。',
            ],
            [
                'user_email' => 'takahashi@example.com',
                'book_isbn' => '9784863940246',
                'rating' => 4,
                'comment' => '一つひとつの習慣について深く考えることができました。',
            ],
            [
                'user_email' => 'yamada@example.com',
                'book_isbn' => '9784863940246',
                'rating' => 5,
                'comment' => '長く読み継がれている理由が分かる内容で、とても参考になりました。',
            ],

            // 坊っちゃん
            [
                'user_email' => 'takahashi@example.com',
                'book_isbn' => '9784101010021',
                'rating' => 4,
                'comment' => '主人公の真っ直ぐな性格が面白く、テンポよく読めました。',
            ],
            [
                'user_email' => 'yamada@example.com',
                'book_isbn' => '9784101010021',
                'rating' => 3,
                'comment' => '昔の作品ならではの表現もありますが、物語を楽しめました。',
            ],

            // サピエンス全史
            [
                'user_email' => 'yamada@example.com',
                'book_isbn' => '9784309226712',
                'rating' => 5,
                'comment' => '人類の歴史を大きな視点から考えることができ、とても興味深かったです。',
            ],
            [
                'user_email' => 'suzuki@example.com',
                'book_isbn' => '9784309226712',
                'rating' => 4,
                'comment' => 'これまで知らなかった歴史の見方を知ることができました。',
            ],
            [
                'user_email' => 'takahashi@example.com',
                'book_isbn' => '9784309226712',
                'rating' => 5,
                'comment' => '壮大なテーマですが説明が分かりやすく、夢中になって読みました。',
            ],

            // Clean Code
            [
                'user_email' => 'suzuki@example.com',
                'book_isbn' => '9784048930598',
                'rating' => 5,
                'comment' => '保守しやすいコードを書くための考え方を体系的に学べました。',
            ],
            [
                'user_email' => 'tanaka@example.com',
                'book_isbn' => '9784048930598',
                'rating' => 4,
                'comment' => 'コードを書くときに意識したいポイントが多く、実践に役立ちそうです。',
            ],
            [
                'user_email' => 'takahashi@example.com',
                'book_isbn' => '9784048930598',
                'rating' => 5,
                'comment' => 'プログラミングの設計や品質について考え直すきっかけになりました。',
            ],

            // 嫌われる勇気
            [
                'user_email' => 'sato@example.com',
                'book_isbn' => '9784478025819',
                'rating' => 5,
                'comment' => '人からどう見られるかを気にしすぎていた自分を見直すきっかけになりました。',
            ],
            [
                'user_email' => 'takahashi@example.com',
                'book_isbn' => '9784478025819',
                'rating' => 4,
                'comment' => '対話形式なので読みやすく、心理学の考え方を楽しく学べました。',
            ],
            [
                'user_email' => 'yamada@example.com',
                'book_isbn' => '9784478025819',
                'rating' => 5,
                'comment' => '自分の考え方を変えるヒントが多く、印象に残る一冊でした。',
            ],

            // 火花
            [
                'user_email' => 'yamada@example.com',
                'book_isbn' => '9784163902302',
                'rating' => 4,
                'comment' => '登場人物それぞれの生き方や葛藤がリアルに描かれていました。',
            ],
            [
                'user_email' => 'suzuki@example.com',
                'book_isbn' => '9784163902302',
                'rating' => 5,
                'comment' => '芸人という世界を舞台にした人間ドラマとして楽しめました。',
            ],
            [
                'user_email' => 'sato@example.com',
                'book_isbn' => '9784163902302',
                'rating' => 3,
                'comment' => '独特な雰囲気の作品でしたが、登場人物の関係性が興味深かったです。',
            ],

            // FACTFULNESS
            [
                'user_email' => 'tanaka@example.com',
                'book_isbn' => '9784822289607',
                'rating' => 5,
                'comment' => '思い込みではなくデータを見ることの大切さを学べました。',
            ],
            [
                'user_email' => 'sato@example.com',
                'book_isbn' => '9784822289607',
                'rating' => 4,
                'comment' => '世界の見方を変えるきっかけになる、分かりやすい内容でした。',
            ],

            // コンテナ物語
            [
                'user_email' => 'takahashi@example.com',
                'book_isbn' => '9784822251468',
                'rating' => 5,
                'comment' => 'コンテナが世界の物流を変えた歴史が分かり、とても興味深かったです。',
            ],
            [
                'user_email' => 'yamada@example.com',
                'book_isbn' => '9784822251468',
                'rating' => 4,
                'comment' => '普段意識していない物流の仕組みについて知ることができました。',
            ],
            [
                'user_email' => 'suzuki@example.com',
                'book_isbn' => '9784822251468',
                'rating' => 5,
                'comment' => '一つの発明が世界経済に大きな影響を与える過程が面白かったです。',
            ],
            [
                'user_email' => 'tanaka@example.com',
                'book_isbn' => '9784822251468',
                'rating' => 3,
                'comment' => '専門的な部分もありましたが、物流について考える良い機会になりました。',
            ],
        ];

        foreach ($reviews as $reviewData) {
            $user = User::where('email', $reviewData['user_email'])->first();
            $book = Book::where('isbn', $reviewData['book_isbn'])->first();

            Review::create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'rating' => $reviewData['rating'],
                'comment' => $reviewData['comment'],
            ]);
        }
    }
}
