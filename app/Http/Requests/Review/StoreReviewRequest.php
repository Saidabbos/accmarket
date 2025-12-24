<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => [
                'required',
                'integer',
                'min:' . config('shop.review.min_rating'),
                'max:' . config('shop.review.max_rating'),
            ],
            'comment' => 'nullable|string|max:' . config('shop.review.max_comment_length'),
        ];
    }

    public function messages(): array
    {
        $minRating = config('shop.review.min_rating');
        $maxRating = config('shop.review.max_rating');
        $maxCommentLength = config('shop.review.max_comment_length');

        return [
            'rating.required' => 'Rating is required.',
            'rating.integer' => 'Rating must be a number.',
            'rating.min' => "Rating must be at least {$minRating} star.",
            'rating.max' => "Rating cannot exceed {$maxRating} stars.",
            'comment.string' => 'Comment must be text.',
            'comment.max' => "Comment cannot exceed {$maxCommentLength} characters.",
        ];
    }
}
