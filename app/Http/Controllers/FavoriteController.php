<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Favorite;

class FavoriteController extends Controller
{
    public function index()
    {
        $books = auth()->user()
            ->favoriteBooks()
            ->paginate(10);

        return view('favorites.index', compact('books'));
    }

    public function toggle(Book $book)
    {
        $favorite = Favorite::where('user_id', auth()->id())
            ->where('book_id', $book->id)
            ->first();

        if ($favorite) {
            $favorite->delete();

            return back()->with('success', 'お気に入りから削除しました。');
        }

        Favorite::create([
            'user_id' => auth()->id(),
            'book_id' => $book->id,
        ]);

        return back()->with('success', 'お気に入りに追加しました。');
    }
}
