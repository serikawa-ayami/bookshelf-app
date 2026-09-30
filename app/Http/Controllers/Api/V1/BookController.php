<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexBookRequest;
use App\Http\Requests\Api\V1\StoreBookRequest;
use App\Http\Requests\Api\V1\UpdateBookRequest;
use App\Http\Resources\BookDetailResource;
use App\Http\Resources\BookListResource;
use App\Models\Book;

class BookController extends Controller
{
    // AP01: 書籍一覧
    public function index(IndexBookRequest $request)
    {
        $query = Book::query()
            ->with('genres')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        // キーワード検索
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');

            $query->where(function ($query) use ($keyword) {
                $query->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%")
                    ->orWhere('isbn', 'like', "%{$keyword}%");
            });
        }

        // ジャンル絞り込み
        if ($request->filled('genre_id')) {
            $query->whereHas('genres', function ($query) use ($request) {
                $query->where('genres.id', $request->input('genre_id'));
            });
        }

        // 1ページあたりの件数
        $perPage = $request->input('per_page', 10);

        $books = $query
            ->latest()
            ->paginate($perPage);

        return BookListResource::collection($books);
    }

    // AP02: 書籍詳細
    public function show(Book $book)
    {
        $book->load([
            'genres',
            'reviews.user',
        ]);

        return new BookDetailResource($book);
    }

    // AP03: 書籍登録
    public function store(StoreBookRequest $request)
    {
        $validated = $request->validated();

        $genres = $validated['genres'];
        unset($validated['genres']);

        $book = Book::create($validated);

        $book->genres()->sync($genres);

        $book->load('genres')
            ->loadAvg('reviews', 'rating')
            ->loadCount('reviews');

        return (new BookListResource($book))
            ->response()
            ->setStatusCode(201);
    }

    // AP04: 書籍更新
    public function update(UpdateBookRequest $request, Book $book)
    {
        $validated = $request->validated();

        $genres = $validated['genres'];
        unset($validated['genres']);

        $book->update($validated);

        $book->genres()->sync($genres);

        $book->load('genres')
            ->loadAvg('reviews', 'rating')
            ->loadCount('reviews');

        return new BookListResource($book);
    }

    // AP05: 書籍削除
    public function destroy(Book $book)
    {
        $book->delete();

        return response()->noContent();
    }
}
