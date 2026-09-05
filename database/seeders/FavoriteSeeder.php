<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $favorites = [
            [
                'user_email' => 'yamada@example.com',
                'book_isbns' => [
                    '9784101010014',
                    '9784873115658',
                    '9784863940246',
                    '9784478025819',
                ],
            ],
            [
                'user_email' => 'suzuki@example.com',
                'book_isbns' => [
                    '9784422100524',
                    '9784101010021',
                    '9784309226712',
                    '9784163902302',
                ],
            ],
            [
                'user_email' => 'tanaka@example.com',
                'book_isbns' => [
                    '9784873115658',
                    '9784048930598',
                    '9784822289607',
                    '9784822251468',
                ],
            ],
            [
                'user_email' => 'sato@example.com',
                'book_isbns' => [
                    '9784422100524',
                    '9784863940246',
                    '9784478025819',
                    '9784822289607',
                ],
            ],
            [
                'user_email' => 'takahashi@example.com',
                'book_isbns' => [
                    '9784101010014',
                    '9784309226712',
                    '9784048930598',
                    '9784822251468',
                ],
            ],
        ];

        foreach ($favorites as $favoriteData) {
            $user = User::where('email', $favoriteData['user_email'])->first();

            $bookIds = Book::whereIn('isbn', $favoriteData['book_isbns'])
                ->pluck('id')
                ->toArray();

            $user->favoriteBooks()->syncWithoutDetaching($bookIds);
        }
    }
}
