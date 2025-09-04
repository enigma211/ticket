<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CannedGroup;
use App\Models\CannedReply;
use App\Models\Message;
use Illuminate\Http\Request;

class CannedReplyController extends Controller
{
	public function index(Request $request)
	{
		$groups = CannedGroup::with('replies')->orderBy('name')->get();

		$query = Message::query()->with(['user:id,name,email', 'ticket:id,tracking_code']);
		$search = (string) $request->input('q', '');
		if ($search !== '') {
			$query->where(function ($sub) use ($search) {
				$sub->where('body', 'like', "%{$search}%")
					->orWhereHas('ticket', function ($t) use ($search) {
						$t->where('tracking_code', 'like', "%{$search}%");
					})
					->orWhereHas('user', function ($u) use ($search) {
						$u->where('name', 'like', "%{$search}%")
							->orWhere('email', 'like', "%{$search}%");
					});
			});
		}
		$messages = $query->latest()->paginate(15)->withQueryString();

		return view('admin.canned.index', compact('groups', 'messages', 'search'));
	}

	public function storeGroup(Request $request)
	{
		abort_unless(auth()->user()?->is_superadmin, 403);
		$validated = $request->validate([
			'name' => ['required', 'string', 'max:255'],
		]);
		CannedGroup::create($validated);
		return back()->with('success', 'گروه پاسخ‌های آماده ایجاد شد.');
	}

	public function updateGroup(Request $request, CannedGroup $group)
	{
		abort_unless(auth()->user()?->is_superadmin, 403);
		$validated = $request->validate([
			'name' => ['required', 'string', 'max:255'],
		]);
		$group->update($validated);
		return back()->with('success', 'گروه بروزرسانی شد.');
	}

	public function destroyGroup(CannedGroup $group)
	{
		abort_unless(auth()->user()?->is_superadmin, 403);
		$group->delete();
		return back()->with('success', 'گروه حذف شد.');
	}

	public function storeReply(Request $request)
	{
		abort_unless(auth()->user()?->is_superadmin, 403);
		$validated = $request->validate([
			'group_id' => ['required', 'exists:canned_groups,id'],
			'title' => ['required', 'string', 'max:255'],
			'body' => ['required', 'string'],
		]);
		CannedReply::create($validated);
		return back()->with('success', 'پاسخ آماده ایجاد شد.');
	}

	public function updateReply(Request $request, CannedReply $reply)
	{
		abort_unless(auth()->user()?->is_superadmin, 403);
		$validated = $request->validate([
			'group_id' => ['required', 'exists:canned_groups,id'],
			'title' => ['required', 'string', 'max:255'],
			'body' => ['required', 'string'],
		]);
		$reply->update($validated);
		return back()->with('success', 'پاسخ آماده بروزرسانی شد.');
	}

	public function destroyReply(CannedReply $reply)
	{
		abort_unless(auth()->user()?->is_superadmin, 403);
		$reply->delete();
		return back()->with('success', 'پاسخ آماده حذف شد.');
	}
}
