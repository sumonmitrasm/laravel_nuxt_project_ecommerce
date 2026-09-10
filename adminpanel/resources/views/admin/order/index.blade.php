@extends('admin.layout.layout')

@section('content')
<div class="app-content main-content"><div class="side-app"><div class="container-fluid main-container">
    <div class="page-header"><div class="page-leftheader"><h4 class="page-title">Orders</h4></div></div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="row">
        @foreach(['pending' => 'warning', 'confirmed' => 'primary', 'processing' => 'info', 'shipped' => 'secondary', 'delivered' => 'success', 'cancelled' => 'danger'] as $status => $color)
            <div class="col-6 col-md-4 col-xl-2 mb-3"><div class="card mb-0"><div class="card-body py-3">
                <div class="text-muted text-uppercase fs-12">{{ $status }}</div>
                <div class="fs-24 fw-bold text-{{ $color }}">{{ number_format((int)($counts[$status] ?? 0)) }}</div>
            </div></div></div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">All Orders</h3></div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin-orders.index') }}" class="row g-2 mb-4">
                <div class="col-md-4"><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Order no, customer, email or phone"></div>
                <div class="col-md-2"><select class="form-select" name="order_status"><option value="">All order statuses</option>@foreach(['pending','confirmed','processing','shipped','delivered','cancelled'] as $status)<option value="{{ $status }}" @selected(request('order_status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
                <div class="col-md-2"><select class="form-select" name="payment_status"><option value="">All payment statuses</option>@foreach(['unpaid','pending','paid','failed','cancelled','refund_pending','refunded'] as $status)<option value="{{ $status }}" @selected(request('payment_status') === $status)>{{ ucwords(str_replace('_',' ',$status)) }}</option>@endforeach</select></div>
                <div class="col-md-2"><select class="form-select" name="payment_method"><option value="">All payments</option><option value="cod" @selected(request('payment_method') === 'cod')>Cash on delivery</option><option value="sslcommerz" @selected(request('payment_method') === 'sslcommerz')>SSLCommerz</option></select></div>
                <div class="col-md-2 d-flex gap-2"><button class="btn btn-primary flex-grow-1">Filter</button><a class="btn btn-light" data-ajax-page href="{{ route('admin-orders.index') }}">Reset</a></div>
            </form>

            <div class="table-responsive"><table class="table table-bordered table-hover align-middle text-nowrap">
                <thead><tr><th>Order</th><th>Customer</th><th>Date</th><th>Items</th><th>Payment</th><th>Status</th><th class="text-end">Total</th><th>Action</th></tr></thead>
                <tbody>
                @forelse($orders as $order)
                    @php $statusColors=['pending'=>'warning','confirmed'=>'primary','processing'=>'info','shipped'=>'secondary','delivered'=>'success','cancelled'=>'danger']; @endphp
                    <tr>
                        <td class="fw-semibold">{{ $order->order_number }}</td>
                        <td>{{ $order->user?->name ?: 'Customer' }}<small class="d-block text-muted">{{ $order->user?->email }}</small></td>
                        <td>{{ optional($order->placed_at)->format('d M Y') }}<small class="d-block text-muted">{{ optional($order->placed_at)->format('h:i A') }}</small></td>
                        <td>{{ $order->items_count }}</td>
                        <td>{{ $order->payment_method === 'cod' ? 'Cash on delivery' : 'SSLCommerz' }}<small class="d-block text-muted">{{ ucwords(str_replace('_',' ',(string) ($order->payment_status ?: 'unknown'))) }}</small></td>
                        <td><span class="badge bg-{{ $statusColors[$order->order_status] ?? 'secondary' }}">{{ ucfirst((string) ($order->order_status ?: 'unknown')) }}</span></td>
                        <td class="text-end">৳{{ number_format((float)$order->grand_total, 2) }}</td>
                        <td><a class="btn btn-sm btn-primary" data-ajax-page href="{{ route('admin-orders.show', $order) }}">View</a></td>
                    </tr>
                @empty<tr><td colspan="8" class="text-center text-muted py-5">No orders found.</td></tr>@endforelse
                </tbody>
            </table></div>
            <div class="mt-3">{{ $orders->links() }}</div>
        </div>
    </div>
</div></div></div>
@endsection
