<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Traits\LogsActivity;
use Illuminate\Auth\Events\Registered;
use App\Models\ActivityLog;

class AuthController extends Controller
{
    use LogsActivity;

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'customer',
        ]);

        try {
            event(new Registered($user));
        } catch (\Exception $e) {
            \Log::error('Failed to send verification email: ' . $e->getMessage());
        }

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'register',
            'description' => 'New user registration',
            'metadata' => [
                'user_id' => $user->id,
                'email' => $user->email
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'message' => 'Registration successful. Please check your email to verify your account.',
            'verification_sent' => true
        ], 201);
    }

    public function verifyEmail(Request $request)
    {
        $user = User::find($request->route('id'));

        if (!$user) {
            return redirect('/login?error=Invalid verification link');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect('/login?message=Email already verified');
        }

        if ($user->markEmailAsVerified()) {
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'email_verified',
                'description' => 'User verified their email',
                'metadata' => [
                    'user_id' => $user->id,
                    'email' => $user->email
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return redirect('/login?message=Email verified successfully');
        }

        return redirect('/login?error=Email verification failed');
    }

    public function resendVerificationEmail(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified'
            ], 400);
        }

        $user->sendEmailVerificationNotification();

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'resend_verification',
            'description' => 'User requested new verification email',
            'metadata' => [
                'user_id' => $user->id,
                'email' => $user->email
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'message' => 'Verification email sent'
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->hasVerifiedEmail()) {
            // Send verification email again
            $user->sendEmailVerificationNotification();

            throw ValidationException::withMessages([
                'email' => ['Please verify your email address first. A new verification email has been sent.'],
            ]);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'login',
            'description' => 'User logged in',
            'metadata' => [
                'user_id' => $user->id,
                'email' => $user->email
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $request->user()->currentAccessToken()->delete();

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'logout',
            'description' => 'User logged out',
            'metadata' => [
                'user_id' => $user->id,
                'email' => $user->email
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
