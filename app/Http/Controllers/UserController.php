<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Cloudinary\Cloudinary;

class UserController extends Controller
{
    //

    private function cloudinary(): Cloudinary
    {
        return new Cloudinary(config('services.cloudinary.url'));
    }

    public function show($user)
    {
        $user = User::where("username", $user)->select('id', 'username', 'profile_picture', 'created_at')->firstOrFail();

        return response()->json($user, 200);
    }

    public function search(Request $request)
    {
        $query = $request->query('q', '');
        $page = (int) $request->query('page', 1);
        $perPage = 10;

        $users = User::where('username', 'like', "%{$query}%")
            ->select('id', 'username', 'profile_picture', 'created_at')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $total = User::where('username', 'like', "%{$query}%")->count();

        return response()->json([
            'users' => $users,
            'hasMore' => ($page * $perPage) < $total,
        ], 200);
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

        $result = $this->cloudinary()->uploadApi()->upload(
            $request->file('profile_picture')->getRealPath(),
            [
                'public_id' => 'questlog/profile_pictures/user_' . $user->id,
                'overwrite' => true,
                'invalidate' => true,
            ]
        );

        $user->profile_picture = $result['secure_url'];
        $user->save();

        return response()->json($user, 200);
    }

    public function removeProfilePicture(Request $request)
    {
        $user = $request->user();

        if ($user->profile_picture) {
            $this->cloudinary()->uploadApi()->destroy(
                'questlog/profile_pictures/user_' . $user->id,
                ['invalidate' => true]
            );
        }

        $user->profile_picture = null;
        $user->save();

        return response()->json($user, 200);
    }
}
