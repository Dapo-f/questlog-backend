<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    //
    public function show($user)
    {
        $user = User::where("username", $user)->select('id', 'username', 'profile_picture', 'created_at')->firstOrFail();

        return response()->json($user, 200);
    }

    public function search(Request $request)
    {
        $query = $request->query('q', '');

        $users = User::where('username', 'like', "%{$query}%")
            ->select('id', 'username', 'profile_picture', 'created_at')
            ->take(40)
            ->get();

        return response()->json($users, 200);
    }

    public function updateUsername(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users,username,' . $request->user()->id,
        ]);

        $user = $request->user();
        $user->username = $validated['username'];
        $user->save();

        return response()->json($user, 200);
    }

    public function updateProfilePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|max:2048',
        ]);

        $user = $request->user();

        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $path = $request->file('profile_picture')->store('profile_pictures', 'public');
        $user->profile_picture = $path;
        $user->save();

        return response()->json($user, 200);
    }

    public function removeProfilePicture(Request $request)
    {
        $user = $request->user();

        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $user->profile_picture = null;
        $user->save();

        return response()->json($user, 200);
    }
}
