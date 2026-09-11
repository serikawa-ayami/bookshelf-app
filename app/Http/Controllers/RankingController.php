<?php

namespace App\Http\Controllers;

use App\Models\Book;

class RankingController extends Controller
{
    public function index()
    {
        $rankedBooks = Book::whereHas('reviews')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderByDesc('reviews_avg_rating')
            ->orderByDesc('reviews_count')
            ->orderBy('title')
            ->limit(10)
            ->get();

        return view('ranking.index', compact('rankedBooks'));
    }
}
