@forelse($adminNotifications as $notification)
    <a href="{{ $notification->order_id ? route('admin-orders.show', $notification->order_id) : ($notification->product_variant_id ? route('inventory.index', ['search' => $notification->variant?->sku]) : '#') }}"
        data-admin-notification data-read-url="{{ route('admin-notifications.read', $notification) }}"
        class="admin-notification-item {{ $notification->read_at ? '' : 'is-unread' }}">
        <span
            class="admin-notification-icon {{ $notification->type === 'order_cancelled' ? 'is-cancelled' : 'is-order' }}"><i
                class="fe {{ $notification->type === 'order_cancelled' ? 'fe-x' : 'fe-shopping-bag' }}"></i></span>
        <span
            class="admin-notification-copy"><strong>{{ $notification->title }}</strong><small>{{ $notification->message }}</small><time>{{ $notification->created_at->diffForHumans() }}</time></span>
        @if (!$notification->read_at)
            <span class="admin-notification-new">New</span>
        @endif
    </a>
@empty<div class="admin-notification-empty"><i class="fe fe-bell-off"></i><span>No notifications yet</span></div>
@endforelse
<div class="admin-notification-footer"><a href="{{ route('admin-notifications.index') }}" data-ajax-page>View all
        notifications</a>
    @if ($unreadAdminNotificationCount)
        <button type="button" data-notifications-read-all data-url="{{ route('admin-notifications.read-all') }}">Mark
            all read</button>
    @endif
</div>
