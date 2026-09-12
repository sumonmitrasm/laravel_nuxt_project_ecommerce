@extends('admin.layout.layout')
@section('content')
    <div class="app-content main-content">
        <div class="side-app">
            <div class="container-fluid main-container">
                <div class="page-header">
                    <div class="page-leftheader">
                        <h4 class="page-title">Inventory</h4>
                        <p class="text-muted">Low stock, stock adjustments and history</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('inventory.index') }}" class="row g-2" data-ajax-filter>
                            <div class="col-md-5"><input class="form-control" name="search" value="{{ request('search') }}"
                                    placeholder="Product name or SKU"></div>
                            <div class="col-md-3"><select class="form-select" name="status">
                                    <option value="">All stock</option>
                                    <option value="out" @selected(request('status') === 'out')>Out of stock</option>
                                    <option value="low" @selected(request('status') === 'low')>Low stock</option>
                                    <option value="healthy" @selected(request('status') === 'healthy')>Healthy</option>
                                </select></div>
                            <div class="col-md-2"><select class="form-select" name="per_page">
                                    @foreach ([10, 20, 50, 100] as $n)
                                        <option value="{{ $n }}" @selected((int) request('per_page', 20) === $n)>{{ $n }}
                                            per page</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Product variants</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Stock</th>
                                    <th>Threshold</th>
                                    <th>Status</th>
                                    <th>Adjust</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($variants as $v)
                                    <tr>
                                        <td>{{ $v->product?->product_name ?: 'Deleted product' }}</td>
                                        <td>{{ $v->sku }}</td>
                                        <td><b>{{ $v->stock }}</b></td>
                                        <td>{{ $v->low_stock_threshold }}</td>
                                        <td>
                                            @if ($v->stock === 0)
                                                <span class="badge bg-danger">Out</span>
                                            @elseif($v->stock <= $v->low_stock_threshold)
                                            <span class="badge bg-warning">Low</span>@else<span
                                                    class="badge bg-success">Healthy</span>
                                            @endif
                                        </td>
                                        <td>
                                            <form data-stock-adjust action="{{ route('inventory.adjust', $v) }}"
                                                class="d-flex gap-1 flex-wrap">@csrf @method('PATCH')<input
                                                    class="form-control form-control-sm stock-input" type="number"
                                                    name="change_quantity" placeholder="+/- Qty" required><input
                                                    class="form-control form-control-sm stock-input" type="number"
                                                    min="0" name="low_stock_threshold"
                                                    value="{{ $v->low_stock_threshold }}" title="Low-stock threshold"
                                                    required><select class="form-select form-select-sm stock-reason"
                                                    name="reason">
                                                    <option value="purchase">Purchase</option>
                                                    <option value="correction">Correction</option>
                                                    <option value="damage">Damage</option>
                                                    <option value="return">Return</option>
                                                    <option value="other">Other</option>
                                                </select><button class="btn btn-sm btn-primary">Update</button></form>
                                        </td>
                                </tr>@empty<tr>
                                        <td colspan="6" class="text-center text-muted py-5">No variants found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">{{ $variants->links() }}</div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent stock history</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Product / SKU</th>
                                    <th>Before</th>
                                    <th>Change</th>
                                    <th>After</th>
                                    <th>Reason</th>
                                    <th>Changed by</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($history as $h)
                                    <tr>
                                        <td>{{ $h->created_at->format('d M Y, h:i A') }}</td>
                                        <td>{{ $h->variant?->product?->product_name }}<small
                                                class="d-block text-muted">{{ $h->variant?->sku }}</small></td>
                                        <td>{{ $h->stock_before }}</td>
                                        <td class="{{ $h->change_quantity > 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $h->change_quantity > 0 ? '+' : '' }}{{ $h->change_quantity }}</td>
                                        <td><b>{{ $h->stock_after }}</b></td>
                                        <td>{{ ucwords(str_replace('_', ' ', $h->reason)) }}</td>
                                        <td>{{ ucfirst($h->changed_by_type) }}</td>
                                </tr>@empty<tr>
                                        <td colspan="7" class="text-center text-muted py-4">No stock history yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .stock-input {
            width: 95px
        }

        .stock-reason {
            width: 115px
        }
    </style>
@endsection
