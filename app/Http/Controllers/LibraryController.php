<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class LibraryController extends Controller
{
    //
    public function index(Request $request)
    {
        // Step 1: Get Authenticated User
        $user = $request->user();
        // Step 2: Fetch all user's library entries
        $libraryEntries = $user->libraryEntries;
        // Step 3: Return the library entries
        return response()->json($libraryEntries, 200);
    }

    public function publicIndex($username)
    {
        // Step 1: find the user by username
        $user = User::where('username', $username)->firstOrFail();

        // Step 2: get their library entries via the relationship
        $libraryEntries = $user->libraryEntries;

        // Step 3: return as JSON
        return response()->json($libraryEntries, 200);
    }

    public function store(Request $request)
    {
        // Step 1: Validate the request
        $validated = $request->validate([
            'rawg_id' => 'required|integer',
            'status' => 'required|in:playing,completed,backlog,dropped,wishlist',
        ]);

        $user = $request->user();

        // Step 2: Check the game isn't already in the user's library
        $existingEntry = $user->libraryEntries()->where('rawg_id', $request->rawg_id)->first();

        if ($existingEntry) {
            return response()->json(['message' => 'Game is already in your library.'], 400);
        }

        // Step 3: Create the library entry
        $libraryEntry = $user->libraryEntries()->create($validated);

        // Step 4: Return the created entry
        return response()->json($libraryEntry, 201);
    }

    public function update(Request $request, $rawg_id)
    {
        // Step 1: Validate the request
        $validated = $request->validate([
            'status' => 'sometimes|in:playing,completed,backlog,dropped,wishlist',
            'hours_played' => 'sometimes|integer|min:0',
            'started_at' => 'sometimes|nullable|date',
            'completed_at' => 'sometimes|nullable|date',
        ]);

        $user = $request->user();

        // Step 2: Find the specific library entry belonging to this user by rawg_id
        $libraryEntry = $user->libraryEntries()->where('rawg_id', $rawg_id)->first();

        if (!$libraryEntry) {
            return response()->json(['message' => 'Library entry not found.'], 404);
        }

        // Step 3: Update the library entry
        $libraryEntry->update($validated);

        // Step 4: Return the updated entry
        return response()->json($libraryEntry, 200);
    }

    public function destroy(Request $request, $rawg_id)
    {
        $user = $request->user();

        // Step 1: Find the specific library entry belonging to this user by rawg_id
        $libraryEntry = $user->libraryEntries()->where('rawg_id', $rawg_id)->first();

        if (!$libraryEntry) {
            return response()->json(['message' => 'Library entry not found.'], 404);
        }

        // Step 2: Delete the library entry
        $libraryEntry->delete();

        // Step 3: Return a success message
        return response()->json(['message' => 'Library entry deleted successfully.'], 200);
    }
}
