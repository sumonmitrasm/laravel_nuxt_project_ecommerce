<?php

namespace App\Http\Controllers;

use App\Models\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminNotificationController extends Controller
{
    public function index()
    {
        $notifications = AdminNotification::query()
            ->where('admin_id', Auth::guard('admin')->id())
            ->with(['order:id,order_number', 'variant:id,sku'])
            ->latest()->paginate(20);

        return view('admin.notification.index', ['title' => 'Notifications', 'notifications' => $notifications]);
    }

    public function read(AdminNotification $notification)
    {
        abort_unless($notification->admin_id === Auth::guard('admin')->id(), 404);
        if (! $notification->read_at) $notification->update(['read_at' => now()]);

        return response()->json([
            'message' => 'Notification marked as read.',
            'redirect_url' => $notification->order_id ? route('admin-orders.show', $notification->order_id) : ($notification->product_variant_id ? route('inventory.index', ['search' => $notification->variant?->sku]) : route('admin-notifications.index')),
        ]);
    }

    public function readAll()
    {
        AdminNotification::query()->where('admin_id', Auth::guard('admin')->id())
            ->whereNull('read_at')->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read.']);
    }
}
