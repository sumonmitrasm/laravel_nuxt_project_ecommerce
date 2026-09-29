<?php

namespace App\Http\Controllers;

use App\Models\VisitorLog;
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

        return response()->json([
            'message' => 'All visitor data has been deleted.',
            'redirect_url' => route('visitors.index'),
        ]);
    }
}
