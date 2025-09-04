<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use Illuminate\Http\Request;

class EmailReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->is_superadmin) { abort(403); }
        $status = $request->input('status');
        $q = trim((string) $request->input('q', ''));
        $query = EmailLog::query();
        if (in_array($status, ['queued','sent','failed'], true)) {
            $query->where('status', $status);
        }
        if ($q !== '') {
            $query->where(function($sub) use ($q){
                $sub->where('to', 'like', "%{$q}%")
                    ->orWhere('subject', 'like', "%{$q}%")
                    ->orWhere('mailable', 'like', "%{$q}%");
            });
        }
        $logs = $query->latest()->paginate(25)->withQueryString();

        $counts = [
            'queued' => EmailLog::where('status','queued')->count(),
            'sent' => EmailLog::where('status','sent')->count(),
            'failed' => EmailLog::where('status','failed')->count(),
        ];

        return view('admin.settings.email_report', compact('logs', 'counts', 'status'));
    }
}


