<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User; // Ensure User model is imported

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();

        // 1. Validation
        $request->validate([
            'username' => 'required|string|max:255|unique:user,username,' . $user->id_user . ',id_user', // Ignore current user's username
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:6|confirmed', // 'confirmed' expects 'new_password_confirmation' field
        ]);

        // 2. Update Username
        if ($request->username !== $user->username) {
            $user->username = $request->username;
        }

        // 3. Update Password (if provided)
        if ($request->filled('new_password')) {
            // Check if current password matches
            // Note: Using password_hash from the model
            if (!Hash::check($request->current_password, $user->password_hash)) {
                return back()->withErrors(['current_password' => 'Password saat ini salah.']);
            }

            $user->password_hash = Hash::make($request->new_password);
        }

        // 4. Save
        $user->save(); // timestamps = false in model

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
