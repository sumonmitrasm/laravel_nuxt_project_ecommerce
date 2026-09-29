<?php

namespace App\Http\Controllers;

use App\Models\AdminLoginActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminLoginActivityController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $activities = AdminLoginActivity::query()->with('admin:id,name,email')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('ip_address', 'like', "%{$search}%")
                    ->orWhere('device', 'like', "%{$search}%")
                    ->orWhereHas('admin', fn ($adminQuery) => $adminQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            }))->latest('logged_in_at')->paginate(30)->withQueryString();

        return view('admin.accounts.login-activity', compact('activities', 'search'));
    }

    public function destroy(): JsonResponse
    {
        AdminLoginActivity::query()->delete();

        return response()->json([
            'message' => 'Login activity has been deleted.',
            'redirect_url' => route('admin-login-activity.index'),
        ]);
    }
}
