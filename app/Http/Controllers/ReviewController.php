<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Review;

class ReviewController extends Controller
{
    //

    public function index($rawg_id)
    {
        $reviews = Review::where('rawg_id', $rawg_id)
            ->with('user:id,username,profile_picture')
            ->latest()
            ->get();

        return response()->json($reviews, 200);
    }

    public function publicIndex($username)
    {
        // Step 1: find the user by username
        $user = User::where('username', $username)->firstOrFail();

        // Step 2: get all the user reviews
        $reviews = $user->reviews;

        // Step 3: return as JSON
        return response()->json($reviews, 200);
    }

    public function store(Request $request)
    {
        //Step 1: Validate the request
        $validated = $request->validate([
            'rawg_id' => 'required|integer',
            'rating' => 'required|numeric|between:1,5',
            'body' => 'required|string|max:2000',
        ]);

        $user = $request->user();

        // Step 2: Check if the user has already reviewed this game
        $existingReview = $user->reviews()->where('rawg_id', $request->rawg_id)->first();
        if ($existingReview) {
            return response()->json(['message' => 'You have already reviewed this game.'], 400);
        }

        // Step 3: Create the review
        $review = $user->reviews()->create($validated);

        // Step 4: Return the created review
        return response()->json($review, 201);
    }

    public function update(Request $request, $id)
    {
        // Step 1: Validate the request
        $validated = $request->validate([
            'rating' => 'sometimes|numeric|between:1,5',
            'body' => 'sometimes|string|max:2000',
        ]);

        $user = $request->user();

        // Step 2: Find the review belonging to this user by ID
        $review = $user->reviews()->where('id', $id)->first();

        if (!$review) {
            return response()->json(['message' => 'Review not found.'], 404);
        }

        // Step 3: Update the review
        $review->update($validated);

        // Step 4: Return the updated review
        return response()->json($review, 200);
    }

    public function recent()
    {
        // Step 1: Get the most recent reviews across all games (not filtered by rawg_id like existing index method)
        $reviews = Review::with('user:id,username,profile_picture')
            ->orderBy('created_at', 'desc') // Step 2: Order by created_at, newest first
            ->take(20)
            ->get();
            
        return response()->json($reviews, 200);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        // Step 1: Find the review belonging to this user by ID
        $review = $user->reviews()->where('id', $id)->first();

        if (!$review) {
            return response()->json(['message' => 'Review not found.'], 404);
        }

        // Step 2: Delete the review
        $review->delete();

        // Step 3: Return a success message
        return response()->json(['message' => 'Review deleted successfully.'], 200);
    }
}
