<?php

namespace App\Http\Controllers;

use App\Models\VisitorLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisitorController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->query('period', 'today');
        $from = match ($period) {
            'week' => now()->subDays(6)->startOfDay(),
            'month' => now()->startOfMonth(),
            default => now()->startOfDay(),
        };

        $logs = VisitorLog::query()
            ->with('user:id,name,email')
            ->where('created_at', '>=', $from)
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $base = VisitorLog::query()->where('created_at', '>=', $from);
        $totalVisits = (clone $base)->count();
        $uniqueVisitors = (clone $base)->distinct('visitor_id')->count('visitor_id');
        $loggedInVisitors = (clone $base)->whereNotNull('user_id')->distinct('user_id')->count('user_id');
        $topPages = (clone $base)->select('path', DB::raw('COUNT(*) as visits'))
            ->groupBy('path')->orderByDesc('visits')->limit(6)->get();

        return view('admin.visitors.index', compact(
            'period', 'logs', 'totalVisits', 'uniqueVisitors', 'loggedInVisitors', 'topPages'
        ));
    }
}
