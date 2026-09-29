<?php

namespace App\Http\Controllers;

use App\Models\VisitorLog;
use App\Models\VisitorSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisitorController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'period' => ['nullable', 'in:today,week,month'],
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'in:15,30,50'],
        ]);
        $period = $filters['period'] ?? 'today';
        $search = trim((string) ($filters['search'] ?? ''));
        $perPage = (int) ($filters['per_page'] ?? 15);
        $from = match ($period) {
            'week' => now()->subDays(6)->startOfDay(),
            'month' => now()->startOfMonth(),
            default => now()->startOfDay(),
        };

        $logs = VisitorLog::query()
            ->with('user:id,name,email')
            ->where('created_at', '>=', $from)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('country', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%")
                        ->orWhere('path', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $base = VisitorLog::query()->where('created_at', '>=', $from);
        $totalVisits = (clone $base)->count();
        $uniqueVisitors = (clone $base)->distinct('visitor_id')->count('visitor_id');
        $loggedInVisitors = (clone $base)->whereNotNull('user_id')->distinct('user_id')->count('user_id');
        $topPages = (clone $base)->select('path', DB::raw('COUNT(*) as visits'))
            ->groupBy('path')->orderByDesc('visits')->limit(6)->get();

        return view('admin.visitors.index', compact(
            'period', 'search', 'perPage', 'logs', 'totalVisits', 'uniqueVisitors', 'loggedInVisitors', 'topPages'
        ));
    }

    public function destroy(): \Illuminate\Http\JsonResponse
    {
        VisitorLog::query()->delete();
        VisitorSession::query()->delete();

        return response()->json([
            'message' => 'All visitor logs and live sessions have been deleted.',
            'redirect_url' => route('visitors.index'),
        ]);
    }

    public function live()
    {
        $since = now()->subMinutes(5);
        $visitors = VisitorSession::query()->with('user:id,name,email')
            ->where('last_seen_at', '>=', $since)->latest('last_seen_at')->paginate(30);

        return view('admin.visitors.live', compact('visitors', 'since'));
    }

    public function trafficSources()
    {
        $rows = VisitorLog::query()
            ->select('referrer', DB::raw('COUNT(*) as visits'), DB::raw('COUNT(DISTINCT visitor_id) as visitors'))
            ->groupBy('referrer')->orderByDesc('visits')->limit(100)->get();

        $sources = $rows->groupBy(fn ($row) => $this->sourceName($row->referrer))
            ->map(fn ($group, $source) => [
                'source' => $source,
                'visits' => $group->sum('visits'),
                'visitors' => $group->sum('visitors'),
            ])->sortByDesc('visits')->values();

        return view('admin.visitors.traffic-sources', compact('sources'));
    }

    public function countries()
    {
        $locations = VisitorLog::query()->whereNotNull('country')
            ->select('country', 'city', DB::raw('COUNT(*) as visits'), DB::raw('COUNT(DISTINCT visitor_id) as visitors'))
            ->groupBy('country', 'city')->orderByDesc('visits')->paginate(50);

        return view('admin.visitors.countries', compact('locations'));
    }

    private function sourceName(?string $referrer): string
    {
        if (! $referrer) return 'Direct';

        $host = strtolower((string) parse_url($referrer, PHP_URL_HOST));
        if ($host === '') return 'Direct';
        if (str_contains($host, 'google.')) return 'Google';
        if (str_contains($host, 'facebook.') || str_contains($host, 'fb.')) return 'Facebook';
        if (str_contains($host, 'instagram.')) return 'Instagram';
        if (str_contains($host, 'youtube.')) return 'YouTube';
        if (str_contains($host, 'tiktok.')) return 'TikTok';

        return $host;
    }
}
