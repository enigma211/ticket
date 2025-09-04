<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        return view('admin.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'national_id' => ['nullable', 'string', 'size:10', 'regex:/^\d{10}$/'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            // current_password not required per request
        ]);

        $update = [];
        if (array_key_exists('first_name', $validated)) { $update['first_name'] = $validated['first_name']; }
        if (array_key_exists('last_name', $validated)) { $update['last_name'] = $validated['last_name']; }
        if (array_key_exists('national_id', $validated)) { $update['national_id'] = $validated['national_id']; }
        if (array_key_exists('mobile', $validated)) { $update['mobile'] = $validated['mobile']; }
        if (array_key_exists('first_name', $update) || array_key_exists('last_name', $update)) {
            $first = $update['first_name'] ?? $user->first_name ?? '';
            $last = $update['last_name'] ?? $user->last_name ?? '';
            $update['name'] = trim($first.' '.$last) ?: $user->name;
        }

        if (!empty($update)) {
            $user->update($update);
        }

        if (!empty($validated['password'] ?? null)) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        return redirect()->route('admin.profile.edit')->with('success', 'حساب کاربری شما بروزرسانی شد.');
    }
}


