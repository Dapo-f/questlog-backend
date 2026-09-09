<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Follow;

class FollowController extends Controller
{
    //
    public function follow(Request $request, $id)
    {

        // Step 1: Get the authenticated user
        $user = $request->user();

        // Step 2: Check if the user is trying to follow themselves
        if ($user->id == $id) {
            return response()->json(['message' => 'You cannot follow yourself.'], 400);
        }

        // Step 3: Check the target user exists
        $targetUser = User::find($id);
        if (!$targetUser) {
            return response()->json(['message' => 'Target user not found.'], 404);
        }

        // Step 4: Check if the user is already following the target user
        if ($user->following()->where('followed_id', $id)->exists()) {
            return response()->json(['message' => 'You are already following this user.'], 400);
        }

        // Step 5: Create the follow relationship
        $user->following()->create(['followed_id' => $id]);

        return response()->json(['message' => 'You are now following this user.'], 200);
    }

    public function unfollow(Request $request, $id)
    {
        // Step 1: Find the follow record where follower_id = current user and followed_id = target id
        $user = $request->user();
        $follow = $user->following()->where('followed_id', $id)->first();

        // Step 2: Check if the follow record exists
        if (!$follow) {
            return response()->json(['message' => 'You are not following this user.'], 400);
        }

        // Step 3: Delete the follow record
        $follow->delete();

        return response()->json(['message' => 'You are no longer following this user.'], 200);
    }

    public function followers($id)
    {
        $followers = Follow::where('followed_id', $id)
            ->with('follower:id,username,profile_picture')
            ->get();

        return response()->json($followers, 200);
    }

    public function following($id)
    {
        $following = Follow::where('follower_id', $id)
            ->with('followed:id,username,profile_picture')
            ->get();

        return response()->json($following, 200);
    }
}
