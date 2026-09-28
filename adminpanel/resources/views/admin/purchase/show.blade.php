@extends('admin.layout.layout')
@section('content')
    <div class="app-content main-content">
        <div class="side-app">
            <div class="container-fluid main-container">
                <div class="page-header">
                    <div class="page-leftheader">
                        <h4 class="page-title">{{ $purchase->purchase_number }}</h4>
                        <p class="text-muted mb-0">{{ $purchase->supplier->name }} ·
                            {{ $purchase->purchase_date->format('d M Y') }}</p>
                    </div>
                    <div class="page-rightheader d-flex gap-2"><a data-ajax-page class="btn btn-outline-primary"
                            href="{{ route('purchases.index') }}">All purchases</a><a data-ajax-page class="btn btn-primary"
                            href="{{ route('purchases.edit', $purchase) }}">Edit information</a></div>
                </div>
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <div class="row">
                    <div class="col-xl-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Purchased products</h3>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-4">Product</th>
                                            <th>SKU</th>
                                            <th class="text-end">Quantity</th>
                                            <th class="text-end">Unit cost</th>
                                            <th class="text-end pe-4">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($purchase->items as $item)
                                            <tr>
                                                <td class="ps-4">{{ $item->product_name }}</td>
                                                <td>{{ $item->sku }}</td>
                                                <td class="text-end">{{ $item->quantity }}</td>
                                                <td class="text-end">৳{{ number_format((float) $item->unit_cost, 2) }}</td>
                                                <td class="text-end pe-4">৳{{ number_format((float) $item->line_total, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Payment history</h3>
                            </div>
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-4">Date</th>
                                            <th>Note</th>
                                            <th class="text-end pe-4">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($purchase->payments as $payment)
                                            <tr>
                                                <td class="ps-4">{{ $payment->paid_at->format('d M Y') }}</td>
                                                <td>{{ $payment->note ?: '—' }}</td>
                                                <td class="text-end pe-4 text-success">
                                                    ৳{{ number_format((float) $payment->amount, 2) }}</td>
                                        </tr>@empty<tr>
                                                <td colspan="3" class="text-center text-muted py-4">No payment yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Payment summary</h3>
                            </div>
                            <div class="card-body payment-summary">
                                <div><span>Total</span><b>৳{{ number_format((float) $purchase->total_amount, 2) }}</b></div>
                                <div><span>Paid</span><b
                                        class="text-success">৳{{ number_format((float) $purchase->paid_amount, 2) }}</b>
                                </div>
                                <div class="due"><span>Due</span><b
                                        class="text-danger">৳{{ number_format((float) $purchase->due_amount, 2) }}</b></div>
                            </div>
                        </div>
                        @if ($purchase->due_amount > 0)
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Pay supplier</h3>
                                </div>
                                <div class="card-body">
                                    <form data-purchase-form method="POST"
                                        action="{{ route('purchases.payments.store', $purchase) }}">@csrf<div
                                            class="mb-3"><label class="form-label">Amount *</label><input
                                                class="form-control" type="number" name="amount" min="0.01"
                                                max="{{ $purchase->due_amount }}" step="0.01" required></div>
                                        <div class="mb-3"><label class="form-label">Payment date *</label><input
                                                class="form-control" type="date" name="paid_at"
                                                value="{{ now()->toDateString() }}" required></div>
                                        <div class="mb-3"><label class="form-label">Note</label><input
                                                class="form-control" name="note"></div><button
                                            class="btn btn-primary w-100">Save payment</button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .payment-summary div {
            display: flex;
            justify-content: space-between;
            padding: 10px 0
        }

        .payment-summary .due {
            border-top: 1px solid #eee;
            margin-top: 4px;
            padding-top: 15px
        }
    </style>
@endsection
