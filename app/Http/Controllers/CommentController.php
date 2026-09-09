<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Review;

class CommentController extends Controller
{
    //
    public function index($review_id)
    {
        $comments = Comment::where('review_id', $review_id)
            ->with('user:id,username,profile_picture')
            ->latest()
            ->get();

        return response()->json($comments, 200);
    }

    public function store(Request $request, $review_id)
    {
        // Step 1: Validate the request
        $validated = $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        $user = $request->user();

        // Step 2: Confirm the review actually exists
        $review = Review::find($review_id);
        if (!$review) {
            return response()->json(['message' => 'Review not found.'], 404);
        }

        // Step 3: Create the comment
        $comment = $user->comments()->create([
            'review_id' => $review_id,
            'body' => $validated['body'],
        ]);

        // Step 4: Return the created comment
        return response()->json($comment, 201);
    }

    public function update(Request $request, $id)
    {
        // Step 1: Validate the request
        $validated = $request->validate([
            'body' => 'sometimes|string|max:1000',
        ]);

        $user = $request->user();

        // Step 2: Find the comment belonging to this user by ID
        $comment = $user->comments()->where('id', $id)->first();

        if (!$comment) {
            return response()->json(['message' => 'Comment not found.'], 404);
        }

        // Step 3: Update the comment
        $comment->update($validated);

        // Step 4: Return the updated comment
        return response()->json($comment, 200);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        // Step 1: Find the comment belonging to this user by ID
        $comment = $user->comments()->where('id', $id)->first();

        if (!$comment) {
            return response()->json(['message' => 'Comment not found.'], 404);
        }

        // Step 2: Delete the comment
        $comment->delete();

        // Step 3: Return a success message
        return response()->json(['message' => 'Comment deleted successfully.'], 200);
    }
}
