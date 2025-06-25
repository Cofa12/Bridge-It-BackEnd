<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class ProfileController extends Controller
{
    //

    public function index(Request $request):JsonResponse
    {
        $user = $request->user();
        return response()->json([
            'user' =>[
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $user->avatar ? asset($user->avatar) : null,
                'bio' => $user->bio,
            ],
            'message' => 'User profile retrieved successfully.',
        ]);
    }
    public function updateAvatar(Request $request):JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'avatar' => 'required|image|max:2048', // 2MB max
        ]);
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }
//        dd($path);
        $path = $request->file('avatar')->store('avatars', 'public');
        $fullPath = asset('storage/' .$path);

        $user->avatar = $path;
        $user->save();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $fullPath,
                'bio' => $user->bio,
            ],
            'message' => 'Avatar updated successfully.',
        ]);
    }


    public function update(Request $request):JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'sometimes|string|max:15|nullable',
            'bio' => 'sometimes|nullable|string|max:255',
        ]);

        $user->update($data);

        return response()->json([
            'user' => $user,
            'message' => 'Profile updated successfully.',
        ]);
    }

    public function updatePassword(Request $request):JsonResponse
    {
        $user = request()->user();
        $data = request()->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (!password_verify($data['current_password'], $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 403);
        }

        $user->update(['password' => bcrypt($data['new_password'])]);

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);

    }


    public function deleteAccount(Request $request):JsonResponse
    {
        $user = $request->user();
        $data = request()->validate([
            'password' => 'required|string',
        ]);
        if(!password_verify($data['password'], $user->password)) {
            return response()->json(['message' => 'Password is incorrect.'], 403);
        }
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
//            dd("exist");
            Storage::disk('public')->delete($user->avatar);
        }

        $user->tokens()->delete();
        $user->userTokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'Profile deleted successfully.',
        ]);
    }




}
