<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Role filter: staff (superadmins or agents) vs normal users
        $role = trim((string) $request->input('role', ''));
        if ($role === 'staff') {
            $query->where(function($q){
                $q->where('is_superadmin', true)->orWhere('is_agent', true);
            });
        } elseif ($role === 'normal') {
            $query->where('is_superadmin', false)->where('is_agent', false);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function edit(User $user)
    {
        $auth = auth()->user();
        if (!$auth) {
            abort(403);
        }
        // Agents can only edit regular end-users, not staff (admins/agents)
        if (!$auth->is_superadmin) {
            if (!$auth->is_agent) {
                abort(403);
            }
            if ($user->is_superadmin || $user->is_agent) {
                abort(403);
            }
        }
        $firstPrefill = $user->first_name;
        $lastPrefill = $user->last_name;
        if ((empty($firstPrefill) && empty($lastPrefill)) && !empty($user->name)) {
            $parts = preg_split('/\s+/', trim((string) $user->name)) ?: [];
            $firstPrefill = $parts[0] ?? '';
            $lastPrefill = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
        }
        return view('admin.users.edit', compact('user', 'firstPrefill', 'lastPrefill'));
    }

    public function update(Request $request, User $user)
    {
        $auth = auth()->user();
        if (!$auth) {
            abort(403);
        }
        // Agents can only edit regular end-users, not staff (admins/agents)
        if (!$auth->is_superadmin) {
            if (!$auth->is_agent) {
                abort(403);
            }
            if ($user->is_superadmin || $user->is_agent) {
                abort(403);
            }
        }
        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'is_superadmin' => ['boolean'],
            'is_agent' => ['boolean'],
            'is_spammer' => ['boolean'],
            'national_id' => ['nullable', 'string', 'size:10', 'regex:/^\\d{10}$/'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        // Prevent removing superadmin from self
        if ($user->id === auth()->id() && !$request->boolean('is_superadmin')) {
            return back()->withErrors(['is_superadmin' => 'نمی‌توانید دسترسی ادمین خود را حذف کنید.']);
        }

        $update = [
            'national_id' => $validated['national_id'] ?? $user->national_id,
            'mobile' => $validated['mobile'] ?? $user->mobile,
        ];
        // Only superadmins can change roles
        if ($auth->is_superadmin) {
            $update['is_superadmin'] = $request->boolean('is_superadmin');
            $update['is_agent'] = $request->boolean('is_agent');
            $update['is_spammer'] = $request->boolean('is_spammer');
        }
        if (array_key_exists('first_name', $validated)) { $update['first_name'] = $validated['first_name']; }
        if (array_key_exists('last_name', $validated)) { $update['last_name'] = $validated['last_name']; }
        if (array_key_exists('first_name', $update) || array_key_exists('last_name', $update)) {
            $first = $update['first_name'] ?? $user->first_name ?? '';
            $last = $update['last_name'] ?? $user->last_name ?? '';
            $update['name'] = trim($first.' '.$last) ?: $user->name;
        }
        $user->update($update);

        // Sync departments (superadmin only, and only for staff users)
        if ($auth->is_superadmin && ($user->is_agent || $user->is_superadmin)) {
            $deptIds = collect($request->input('department_ids', []))->filter()->map(fn($v) => (int) $v)->unique()->values()->all();
            $user->departments()->sync($deptIds);
        }

        if (!empty($validated['password'] ?? null)) {
            $user->update([
                'password' => Hash::make($validated['password'])
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'نقش‌های کاربر بروزرسانی شد.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'is_superadmin' => ['boolean'],
            'is_agent' => ['boolean'],
            'is_spammer' => ['boolean'],
            'national_id' => ['nullable', 'string', 'size:10', 'regex:/^\\d{10}$/'],
            'mobile' => ['nullable', 'string', 'max:20'],
        ]);

        User::create([
            'name' => trim(($validated['first_name'] ?? '').' '.($validated['last_name'] ?? '')),
            'first_name' => $validated['first_name'] ?? null,
            'last_name' => $validated['last_name'] ?? null,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_superadmin' => $request->boolean('is_superadmin'),
            'is_agent' => $request->boolean('is_agent'),
            'is_spammer' => $request->boolean('is_spammer'),
            'national_id' => $validated['national_id'] ?? null,
            'mobile' => $validated['mobile'] ?? null,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'کاربر جدید ایجاد شد.');
    }

    public function destroy(User $user)
    {
        // Prevent deleting self
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'نمی‌توانید حساب خود را حذف کنید.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'کاربر حذف شد.');
    }

    public function tickets(User $user)
    {
        $tickets = Ticket::query()
            ->where('user_id', $user->id)
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('admin.users.tickets', compact('user', 'tickets'));
    }
}