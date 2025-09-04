<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $months = (int) $request->integer('months', 3);
        if (!in_array($months, [3, 6, 12], true)) {
            $months = 3;
        }

        // Build month range with Carbon, then convert to Jalali for proper counting
        $startDate = now()->startOfMonth()->subMonths($months - 1);
        
        $labels = []; // Gregorian keys (Y-m) for message grouping
        $labelsDisplay = []; // Jalali display labels
        for ($i = 0; $i < $months; $i++) {
            $carbonMonth = $startDate->copy()->addMonths($i);
            $labels[] = $carbonMonth->format('Y-m'); // Gregorian for DB query
            $labelsDisplay[] = jdate($carbonMonth)->format('Y/m'); // Jalali for display
        }

        // Initialize counters
        $topicsIncomingCounts = array_fill(0, $months, 0); // tickets created by customers
        $topicsAnsweredCounts = array_fill(0, $months, 0); // distinct tickets answered by agents
        $messagesTotalCounts = array_fill(0, $months, 0); // total public messages

        // 1) Customer tickets received per month (موضوعات دریافت‌شده)
        $tickets = Ticket::with('user')
            ->whereBetween('created_at', [$startDate->copy()->startOfDay(), now()->endOfMonth()->endOfDay()])
            ->whereHas('user', function ($q) {
                $q->where('is_agent', false)->where('is_superadmin', false);
            })
            ->get();

        foreach ($tickets as $ticket) {
            $monthKey = $ticket->created_at->format('Y-m');
            $index = array_search($monthKey, $labels, true);
            if ($index === false) {
                continue;
            }
            $topicsIncomingCounts[$index]++;
        }

        // 2) All public messages in range for total count and answered topics detection
        $messages = Message::with('user')
            ->where('visibility', 'public')
            ->whereBetween('created_at', [$startDate->copy()->startOfDay(), now()->endOfMonth()->endOfDay()])
            ->get();

        // Track unique ticket IDs per month for answered topics
        $answeredTicketIdsPerMonth = array_fill(0, $months, []);

        foreach ($messages as $message) {
            $monthKey = $message->created_at->format('Y-m');
            $index = array_search($monthKey, $labels, true);
            if ($index === false) {
                continue;
            }
            
            // Count all public messages (customer + agent)
            $messagesTotalCounts[$index]++;

            // Track tickets that have been answered by agents
            $user = $message->user;
            $isAgentish = $user && ($user->is_agent || $user->is_superadmin);
            if ($isAgentish) {
                $answeredTicketIdsPerMonth[$index][$message->ticket_id] = true;
            }
        }

        // Convert answered ticket ID sets to counts
        foreach ($answeredTicketIdsPerMonth as $i => $set) {
            $topicsAnsweredCounts[$i] = count($set);
        }

        return view('admin.reports.index', [
            'months' => $months,
            'labels' => $labelsDisplay, // Use Jalali labels for display
            'topics_incoming' => $topicsIncomingCounts,
            'topics_answered' => $topicsAnsweredCounts,
            'messages_total' => $messagesTotalCounts,
        ]);
    }

    public function managers(Request $request)
    {
        // Month filter (YYYY-MM). Default: current month
        $month = trim((string) $request->input('month', now()->format('Y-m')));
        try {
            $start = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Throwable $e) {
            $start = now()->startOfMonth();
        }
        $end = $start->copy()->endOfMonth();

        // Load agents
        $staff = User::query()
            ->where(function($q){ $q->where('is_agent', true)->orWhere('is_superadmin', true); })
            ->orderBy('first_name')->orderBy('last_name')
            ->get(['id','first_name','last_name','name']);
        $staffIds = $staff->pluck('id')->all();

        // Sessions: better presence calculation using session activity
        $sessions = DB::table('sessions')
            ->select('user_id','last_activity')
            ->whereIn('user_id', $staffIds)
            ->whereBetween('last_activity', [$start->copy()->startOfDay()->timestamp, $end->copy()->endOfDay()->timestamp])
            ->get();

        $presenceByUserDay = [];
        $sessionLifetime = config('session.lifetime', 120); // minutes
        
        foreach ($sessions as $row) {
            if (!$row->user_id) continue;
            $dayKey = \Carbon\Carbon::createFromTimestamp((int)$row->last_activity)->format('Y-m-d');
            
            // Track first and last activity per day
            if (!isset($presenceByUserDay[$row->user_id][$dayKey])) {
                $presenceByUserDay[$row->user_id][$dayKey] = [
                    'first_activity' => (int)$row->last_activity,
                    'last_activity' => (int)$row->last_activity,
                    'activity_count' => 1
                ];
            } else {
                $presenceByUserDay[$row->user_id][$dayKey]['first_activity'] = min(
                    $presenceByUserDay[$row->user_id][$dayKey]['first_activity'], 
                    (int)$row->last_activity
                );
                $presenceByUserDay[$row->user_id][$dayKey]['last_activity'] = max(
                    $presenceByUserDay[$row->user_id][$dayKey]['last_activity'], 
                    (int)$row->last_activity
                );
                $presenceByUserDay[$row->user_id][$dayKey]['activity_count']++;
            }
        }

        // Replies per day per agent
        $msgs = Message::query()
            ->with('user:id,is_agent,is_superadmin')
            ->where('visibility','public')
            ->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->whereHas('user', function($q){ $q->where('is_agent', true)->orWhere('is_superadmin', true); })
            ->get(['id','user_id','created_at']);
        $repliesByUserDay = [];
        foreach ($msgs as $m) {
            $dayKey = $m->created_at->format('Y-m-d');
            $repliesByUserDay[$m->user_id][$dayKey] = ($repliesByUserDay[$m->user_id][$dayKey] ?? 0) + 1;
        }

        // Build final structure
        $agents = [];
        foreach ($staff as $user) {
            $days = [];
            $cursor = $start->copy();
            while ($cursor->lte($end)) {
                $dayKey = $cursor->format('Y-m-d');
                $presence = $presenceByUserDay[$user->id][$dayKey] ?? null;
                $minutes = null;
                
                if ($presence) {
                    // Calculate presence minutes based on activity span
                    $firstActivity = $presence['first_activity'];
                    $lastActivity = $presence['last_activity'];
                    $activityCount = $presence['activity_count'];
                    
                    // If multiple activities, estimate based on span + session lifetime
                    if ($activityCount > 1) {
                        $spanMinutes = (int) floor(($lastActivity - $firstActivity) / 60);
                        // Add session lifetime for each activity (max 8 hours per day)
                        $estimatedMinutes = min($spanMinutes + ($activityCount * $sessionLifetime), 480);
                    } else {
                        // Single activity: assume minimum session lifetime
                        $estimatedMinutes = $sessionLifetime;
                    }
                    $minutes = max($estimatedMinutes, 30); // minimum 30 minutes
                }
                
                $days[$dayKey] = [
                    'date_display' => jdate($cursor)->format('Y/m/d'),
                    'minutes' => $minutes, // null means no presence
                    'replies' => (int) ($repliesByUserDay[$user->id][$dayKey] ?? 0),
                ];
                $cursor->addDay();
            }
            // Summaries - improved calculation
            $activeDays = collect($days)->filter(fn($d) => $d['minutes'] !== null && $d['minutes'] > 0)->count();
            $totalMinutes = collect($days)->reduce(fn($c,$d) => $c + (int) ($d['minutes'] ?? 0), 0);
            $totalReplies = collect($days)->reduce(fn($c,$d) => $c + (int) $d['replies'], 0);

            $agents[] = [
                'id' => $user->id,
                'name' => ($user->first_name || $user->last_name) ? trim(($user->first_name ?? '').' '.($user->last_name ?? '')) : ($user->name ?? ('کاربر #'.$user->id)),
                'days' => $days,
                'active_days' => $activeDays,
                'total_minutes' => $totalMinutes,
                'total_replies' => $totalReplies,
            ];
        }

        // Sort by total_minutes desc
        usort($agents, fn($a,$b) => ($b['total_minutes'] <=> $a['total_minutes']) ?: ($b['total_replies'] <=> $a['total_replies']));

        // CSV export when requested
        if ($request->boolean('export')) {
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="managers-report-'.$start->format('Y-m').'.csv"',
            ];

            $callback = function() use ($agents, $start, $end) {
                $out = fopen('php://output', 'w');
                // UTF-8 BOM for Excel compatibility
                fwrite($out, chr(0xEF).chr(0xBB).chr(0xBF));
                // Header
                fputcsv($out, ['نام', 'تاریخ (شمسی)', 'دقیقه حضور', 'تعداد پاسخ‌ها']);
                // Rows per agent per day
                foreach ($agents as $agent) {
                    foreach ($agent['days'] as $dayKey => $day) {
                        fputcsv($out, [
                            $agent['name'],
                            $day['date_display'],
                            $day['minutes'] !== null ? $day['minutes'] : 0,
                            (int) $day['replies'],
                        ]);
                    }
                }
                // Summary row per agent
                fputcsv($out, []);
                fputcsv($out, ['خلاصه به تفکیک کارشناس']);
                fputcsv($out, ['نام', 'روزهای فعال', 'جمع دقیقه حضور', 'جمع پاسخ‌ها']);
                foreach ($agents as $agent) {
                    fputcsv($out, [
                        $agent['name'],
                        $agent['active_days'],
                        $agent['total_minutes'],
                        $agent['total_replies'],
                    ]);
                }
                fclose($out);
            };

            return response()->stream($callback, 200, $headers);
        }

        return view('admin.reports.managers', [
            'month' => $start->format('Y-m'),
            'agents' => $agents,
        ]);
    }
}


