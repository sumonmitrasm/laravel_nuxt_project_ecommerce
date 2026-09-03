<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RegisterRequest;
use App\Models\User;
use App\Support\ImageOptimizer;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthController extends Controller
{
    public function __construct(private readonly ImageOptimizer $images) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->sendEmailVerificationNotification();

        return response()->json([
            'status' => true,
            'message' => 'Account created. Please verify your email before signing in.',
            'email' => $user->email,
        ], 201);
    }

    public function verifyEmail(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = User::findOrFail($id);
        abort_unless(hash_equals($hash, sha1($user->getEmailForVerification())), 403);

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        if (! (bool) $user->status) {
            $user->status = 1;
            $user->save();
        }

        $frontend = rtrim((string) config('app.frontend_url', 'http://localhost:3000'), '/');

        return redirect()->away($frontend.'/login?verified=1');
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $validated = $request->validate(['email' => ['required', 'email']]);
        $user = User::where('email', $validated['email'])->first();

        if ($user && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        return response()->json([
            'status' => true,
            'message' => 'If the account is awaiting verification, a new email has been sent.',
        ]);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Email address or password is incorrect.',
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => 'Please verify your email address before signing in.',
            ]);
        }

        if (! (bool) $user->status) {
            throw ValidationException::withMessages([
                'email' => 'Your account is inactive. Please contact support to activate your account.',
            ]);
        }

        Auth::guard('web')->login($user, (bool) ($validated['remember'] ?? false));
        $request->session()->regenerate();

        return response()->json([
            'status' => true,
            'message' => 'Signed in successfully.',
            'user' => $this->userPayload($user),
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'user' => $this->userPayload($request->user()),
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->merge([
            'name' => trim((string) $request->input('name')),
            'mobile' => preg_replace('/[\s\-()]/', '', (string) $request->input('mobile')),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:20', "regex:/^[\pL\pM\s.'-]+$/u"],
            'mobile' => ['nullable', 'string', 'regex:/^\+?[0-9]{10,15}$/'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=150,min_height=150,max_width=3000,max_height=3000'],
        ], [
            'name.regex' => 'The name may only contain letters, spaces, apostrophes, hyphens and dots.',
            'mobile.regex' => 'Please enter a valid mobile number.',
            'image.max' => 'The profile image must not be larger than 2 MB.',
            'image.dimensions' => 'The image dimensions must be between 150 x 150 and 3000 x 3000 pixels.',
        ]);

        $oldImage = $user->image;
        $newImage = null;

        try {
            if ($request->hasFile('image')) {
                $newImage = $this->images->store(
                    $request->file('image'),
                    'admin/userimage',
                    'user',
                    600,
                    600,
                    84,
                );
            }

            DB::transaction(function () use ($user, $validated, $newImage): void {
                $user->name = $validated['name'];
                $user->mobile = $validated['mobile'] ?: null;

                if ($newImage) {
                    $user->image = $newImage;
                }

                $user->save();
            });
        } catch (Throwable $exception) {
            $this->images->delete($newImage, 'admin/userimage');

            throw $exception;
        }

        if ($newImage && $oldImage) {
            $this->images->delete($oldImage, 'admin/userimage');
        }

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully.',
            'user' => $this->userPayload($user->fresh()),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['status' => true, 'message' => 'Signed out successfully.']);
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'mobile' => $user->mobile,
            'image' => $user->image,
            'image_url' => $user->image
                ? asset('admin/userimage/'.$user->image)
                : null,
            'email_verified_at' => $user->email_verified_at?->toISOString(),
        ];
    }
}
