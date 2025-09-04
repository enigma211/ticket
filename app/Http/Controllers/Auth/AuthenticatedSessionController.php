<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
	public function create()
	{
		return view('auth.login');
	}

	public function createAdmin()
	{
		return view('auth.admin-login');
	}

	public function store(Request $request)
	{
		$credentials = $request->validate([
			'email' => ['required', 'string', 'email'],
			'password' => ['required', 'string'],
		]);

		$remember = (bool) $request->boolean('remember');

		// Distinguish wrong email vs wrong password (localized)
		$candidate = User::where('email', $credentials['email'])->first();
		if (!$candidate) {
			return back()->withErrors(['email' => 'ایمیل وارد شده یافت نشد.'])->onlyInput('email');
		}
		if (!Hash::check($credentials['password'], $candidate->password)) {
			return back()->withErrors(['password' => 'رمز عبور نادرست است.'])->onlyInput('email');
		}
		if (! Auth::attempt($credentials, $remember)) {
			return back()->withErrors(['email' => 'ورود ناموفق بود. دوباره تلاش کنید.'])->onlyInput('email');
		}

		$request->session()->regenerate();

		$user = $request->user();
		// If request came from /admin/login, require staff role
		if ($request->routeIs('admin.login') || $request->routeIs('admin.login.store')) {
			if (!$user || !($user->is_agent || $user->is_superadmin)) {
				Auth::logout();
				return back()->withErrors(['email' => 'دسترسی این بخش فقط برای کارکنان است.']);
			}
		}

		// If request came from normal /login but user is staff, force using admin login URL
		if ($request->routeIs('login') && $user && ($user->is_agent || $user->is_superadmin)) {
			Auth::logout();
			return back()->withErrors(['email' => 'شما اجازه ورود ندارید.'])->onlyInput('email');
		}

		if ($user && ($user->is_agent || $user->is_superadmin)) {
			return redirect()->intended(route('admin.dashboard'));
		}

		// For normal users, send to user tickets list (not admin)
		return redirect()->route('tickets.index');
	}

	public function storeAdmin(Request $request)
	{
		$credentials = $request->validate([
			'email' => ['required', 'string', 'email'],
			'password' => ['required', 'string'],
		]);
		$remember = (bool) $request->boolean('remember');
		$candidate = User::where('email', $credentials['email'])->first();
		if (!$candidate) {
			return back()->withErrors(['email' => 'ایمیل وارد شده یافت نشد.'])->onlyInput('email');
		}
		if (!Hash::check($credentials['password'], $candidate->password)) {
			return back()->withErrors(['password' => 'رمز عبور نادرست است.'])->onlyInput('email');
		}
		if (! Auth::attempt($credentials, $remember)) {
			return back()->withErrors(['email' => 'ورود ناموفق بود. دوباره تلاش کنید.'])->onlyInput('email');
		}
		$request->session()->regenerate();
		$user = $request->user();
		if (!$user || !($user->is_agent || $user->is_superadmin)) {
			Auth::logout();
			return back()->withErrors(['email' => 'شما اجازه ورود ندارید.'])->onlyInput('email');
		}
		return redirect()->intended(route('admin.dashboard'));
	}

	public function destroy(Request $request)
	{
		Auth::guard('web')->logout();

		$request->session()->invalidate();
		$request->session()->regenerateToken();

		// Proactively clear session and remember-me cookies on client
		$sessionCookie = config('session.cookie');
		$recallerCookie = Auth::guard('web')->getRecallerName();
		Cookie::queue(Cookie::forget($sessionCookie));
		Cookie::queue(Cookie::forget('XSRF-TOKEN'));
		if (!empty($recallerCookie)) {
			Cookie::queue(Cookie::forget($recallerCookie));
		}

		return redirect()->route('login');
	}
}
