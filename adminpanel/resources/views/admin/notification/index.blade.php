@extends('admin.layout.layout')
@section('content')
<div class="app-content main-content"><div class="side-app"><div class="container-fluid main-container">
<div class="page-header"><div class="page-leftheader"><h4 class="page-title">Notifications</h4></div><div class="page-rightheader"><button class="btn btn-primary" data-notifications-read-all data-url="{{ route('admin-notifications.read-all') }}">Mark all as read</button></div></div>
<div class="card"><div class="card-body p-0"><div class="list-group list-group-flush">
@forelse($notifications as $notification)
<a href="{{ $notification->order_id ? route('admin-orders.show',$notification->order_id) : '#' }}" data-admin-notification data-read-url="{{ route('admin-notifications.read',$notification) }}" class="list-group-item list-group-item-action d-flex align-items-start gap-3 py-3 {{ $notification->read_at ? '' : 'bg-light' }}">
<span class="avatar avatar-md rounded-circle {{ $notification->type === 'order_cancelled' ? 'bg-danger' : 'bg-success' }} text-white"><i class="fe {{ $notification->type === 'order_cancelled' ? 'fe-x' : 'fe-shopping-bag' }}"></i></span><span class="flex-grow-1"><strong>{{ $notification->title }}</strong><span class="d-block text-muted">{{ $notification->message }}</span><small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small></span>@if(!$notification->read_at)<span class="badge bg-primary">New</span>@endif
</a>
@empty<div class="text-center text-muted py-5">No notifications yet.</div>@endforelse
</div></div><div class="card-footer">{{ $notifications->links() }}</div></div>
</div></div></div>
@endsection
