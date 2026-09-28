@extends('admin.layout.layout')
@section('content')
    <div class="app-content main-content">
        <div class="side-app">
            <div class="container-fluid main-container">
                <div class="page-header">
                    <div class="page-leftheader">
                        <h4 class="page-title">Suppliers</h4>
                        <p class="text-muted mb-0">Keep supplier contact details and outstanding balance.</p>
                    </div>
                    <div class="page-rightheader"><a data-ajax-page class="btn btn-outline-primary"
                            href="{{ route('purchases.index') }}">Purchases</a></div>
                </div>
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <div class="row">
                    <div class="col-xl-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Add supplier</h3>
                            </div>
                            <div class="card-body">
                                <form data-purchase-form method="POST" action="{{ route('suppliers.store') }}">@csrf
                                    <div class="mb-3"><label class="form-label">Supplier name *</label><input
                                            class="form-control" name="name" value="{{ old('name') }}" required></div>
                                    <div class="mb-3"><label class="form-label">Phone</label><input class="form-control"
                                            name="phone" value="{{ old('phone') }}"></div>
                                    <div class="mb-3"><label class="form-label">Email</label><input type="email"
                                            class="form-control" name="email" value="{{ old('email') }}"></div>
                                    <div class="mb-3"><label class="form-label">Address</label>
                                        <textarea class="form-control" name="address" rows="3">{{ old('address') }}</textarea>
                                    </div>
                                    <button class="btn btn-primary w-100">Save supplier</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Supplier list</h3>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-4">Supplier</th>
                                            <th>Contact</th>
                                            <th class="text-end">Purchased</th>
                                            <th class="text-end">Due</th>
                                            <th class="text-end pe-4">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($suppliers as $supplier)
                                            <tr>
                                                <td class="ps-4"><b>{{ $supplier->name }}</b><small
                                                        class="d-block text-muted">{{ $supplier->address }}</small></td>
                                                <td>{{ $supplier->phone ?: '—' }}<small
                                                        class="d-block text-muted">{{ $supplier->email }}</small></td>
                                                <td class="text-end">
                                                    ৳{{ number_format((float) $supplier->total_purchased, 2) }}</td>
                                                <td class="text-end"><b
                                                        class="{{ $supplier->total_due > 0 ? 'text-danger' : 'text-success' }}">৳{{ number_format((float) $supplier->total_due, 2) }}</b>
                                                </td>
                                                <td class="text-end pe-4"><a data-ajax-page
                                                        class="btn btn-sm btn-outline-primary"
                                                        href="{{ route('suppliers.edit', $supplier) }}">Edit</a></td>
                                        </tr>@empty<tr>
                                                <td colspan="5" class="text-center text-muted py-5">No suppliers yet.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer">{{ $suppliers->links() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
