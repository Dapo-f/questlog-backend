<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PasswordReset;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCodeMail;
use App\Mail\PasswordResetMail;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //
    public function register(Request $request)
    {
        // Step 1: Validate
        $request->validate([
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|confirmed|string|min:8',
            'date_of_birth' => 'required|date|before:today',
            'profile_picture' => 'nullable|image|max:2048',
        ]);

        // Step 2: Create the user
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
            'date_of_birth' => $request->date_of_birth,
            'profile_picture' => $request->hasFile('profile_picture')
                ? $request->file('profile_picture')->store('profile_pictures', 'public')
                : null,
        ]);

        // Step 3: Generate a 6 digit code and create verification row
        $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->accountVerification()->create([
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
        ]);

        // Step 4: Send the email
        Mail::to($user->email)->send(new VerificationCodeMail($code));

        // Step 5: Return a response
        return response()->json([
            'message' => 'Registration successful. Please verify your email.',
        ], 201);
    }

    public function verifyEmail(Request $request)
    {
        // Step 1: Validate
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        // Step 2: Find the user
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Step 3: Check if the code matches and is not expired
        $verification = $user->accountVerification;

        if (!$verification || $verification->code !== $request->code) {
            return response()->json(['message' => 'Invalid verification code.'], 400);
        }

        if ($verification->expires_at < now()) {
            return response()->json(['message' => 'Verification code has expired.'], 400);
        }

        // Step 4: Mark the user as verified and delete the verification row
        $user->is_verified = true;
        $user->save();
        $verification->delete();

        // Step 5: Return a response
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Email verified successfully.',
            'token' => $token,
        ], 200);
    }

    public function resendCode(Request $request)
    {
        // Step 1: Validate
        $request->validate([
            'email' => 'required|email',
        ]);

        // Step 2: Find the user
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if ($user->is_verified) {
            return response()->json(['message' => 'Account is already verified.'], 400);
        }

        // Step 3: Generate a new 6 digit code and update the verification row
        $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->accountVerification()->updateOrCreate(
            ['user_id' => $user->id,],
            [
                'code' => $code,
                'expires_at' => now()->addMinutes(10),
            ]
        );

        // Step 4: Send the email
        Mail::to($user->email)->send(new VerificationCodeMail($code));

        // Step 5: Return a response
        return response()->json([
            'message' => 'New verification code sent.',
        ], 200);
    }

    public function login(Request $request)
    {
        // Step 1: Validate
        $request->validate([
            'identifier' => 'required|string',
            'password' => 'required|string',
        ]);

        // Step 2: Find the user
        $user = User::where('email', $request->identifier)
            ->orWhere('username', $request->identifier)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Step 3: Verify the password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        // Step 4: Check if the user is verified
        if (!$user->is_verified) {
            return response()->json(['message' => 'Account is not verified. Please verify your email.'], 403);
        }

        // Step 5: Create a token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Step 6: Return the token
        return response()->json([
            'message' => 'Login successful.',
            'token' => $token,
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ], 200);
    }

    public function forgotPassword(Request $request)
    {
        // Step 1: Validate
        $request->validate([
            'email' => 'required|email',
        ]);

        // Step 2: Find the user
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Step 3: Generate a password reset token
        $token = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // Step 4: Store the token in the database 
        PasswordReset::updateOrCreate(
            ['email' => $user->email],
            ['token' => $token, 'expires_at' => now()->addMinutes(10)]
        );

        // Step 5: Send the email
        Mail::to($user->email)->send(new PasswordResetMail($token));

        // Step 6: Return a response
        return response()->json([
            'message' => 'Password reset email sent.',
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        // Step 1: Validate
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Step 2: Find the password reset entry
        $passwordReset = PasswordReset::where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$passwordReset) {
            return response()->json(['message' => 'Invalid token or email.'], 400);
        }

        if ($passwordReset->expires_at < now()) {
            return response()->json(['message' => 'Token has expired.'], 400);
        }

        // Step 3: Find the user
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Step 4: Update the user's password
        $user->password = $request->password;
        $user->save();

        // Step 5: Delete the password reset entry
        $passwordReset->delete();

        // Step 6: Return a response
        return response()->json([
            'message' => 'Password has been reset successfully.',
        ], 200);
    }
}
