<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewLike;

class ReviewLikeController extends Controller
{
    public function toggle(Review $review)
    {
        $like = ReviewLike::where('user_id', auth()->id())
            ->where('review_id', $review->id)
            ->first();

        if ($like) {
            $like->delete();

            return back();
        }

        ReviewLike::create([
            'user_id' => auth()->id(),
            'review_id' => $review->id,
        ]);

        return back();
    }
}
