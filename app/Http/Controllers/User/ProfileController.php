<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('user.profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if ($user->is_agent || $user->is_superadmin) {
            abort(403);
        }
        // 7-day lock
        if ($user->profile_locked_until && now()->lt($user->profile_locked_until)) {
            $diffDays = now()->diffInDays($user->profile_locked_until);
            return back()->withErrors(['profile' => "امکان ویرایش پروفایل تا {$diffDays} روز دیگر فعال می‌شود."]);
        }

        $validated = $request->validate([
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'national_id' => ['nullable', 'string', 'size:10', 'regex:/^\\d{10}$/', 'unique:users,national_id,' . $user->id],
            'mobile' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $update = [];
        if (array_key_exists('email', $validated)) { $update['email'] = $validated['email']; }
        if (array_key_exists('first_name', $validated)) { $update['first_name'] = $validated['first_name']; }
        if (array_key_exists('last_name', $validated)) { $update['last_name'] = $validated['last_name']; }
        if (array_key_exists('national_id', $validated)) { $update['national_id'] = $validated['national_id']; }
        if (array_key_exists('mobile', $validated)) { $update['mobile'] = $validated['mobile']; }
        if (!empty($validated['password'] ?? null)) { $update['password'] = Hash::make($validated['password']); }
        if (array_key_exists('first_name', $validated) || array_key_exists('last_name', $validated)) {
            $first = $validated['first_name'] ?? $user->first_name ?? '';
            $last = $validated['last_name'] ?? $user->last_name ?? '';
            $update['name'] = trim($first.' '.$last) ?: $user->name;
        }
        if (!empty($update)) {
            $update['profile_locked_until'] = now()->addDays(7);
            $user->update($update);
        }
        return back()->with('success', 'پروفایل بروزرسانی شد. امکان ویرایش مجدد پس از ۷ روز.');
    }
}


