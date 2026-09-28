@extends('admin.layout.layout')
@section('content')
    <div class="app-content main-content">
        <div class="side-app">
            <div class="container-fluid main-container">
                <div class="page-header">
                    <div class="page-leftheader">
                        <h4 class="page-title">Edit purchase</h4>
                        <p class="text-muted mb-0">{{ $purchase->purchase_number }} · quantity and cost changes update stock
                            and due automatically.</p>
                    </div>
                </div>
                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif
                <form data-purchase-form method="POST" action="{{ route('purchases.update', $purchase) }}">@csrf
                    @method('PUT')<div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Products received</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Product</th>
                                        <th>SKU</th>
                                        <th width="190">Quantity</th>
                                        <th width="220">Unit cost</th>
                                        <th class="text-end pe-4">New total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($purchase->items as $item)
                                        <tr>
                                            <td class="ps-4">{{ $item->product_name }}<input type="hidden"
                                                    name="items[{{ $loop->index }}][id]" value="{{ $item->id }}"></td>
                                            <td>{{ $item->sku }}</td>
                                            <td><input class="form-control js-quantity" type="number"
                                                    name="items[{{ $loop->index }}][quantity]" min="1"
                                                    value="{{ old('items.' . $loop->index . '.quantity', $item->quantity) }}"
                                                    required></td>
                                            <td><input class="form-control js-cost" type="number"
                                                    name="items[{{ $loop->index }}][unit_cost]" min="0"
                                                    step="0.01"
                                                    value="{{ old('items.' . $loop->index . '.unit_cost', $item->unit_cost) }}"
                                                    required></td>
                                            <td class="text-end pe-4 js-line-total">
                                                ৳{{ number_format((float) $item->line_total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3"><label class="form-label">Purchase date *</label><input
                                        class="form-control" type="date" name="purchase_date"
                                        value="{{ old('purchase_date', $purchase->purchase_date->toDateString()) }}"
                                        required></div>
                                <div class="col-12 mb-3"><label class="form-label">Note</label>
                                    <textarea class="form-control" name="note" rows="4">{{ old('note', $purchase->note) }}</textarea>
                                </div>
                            </div><button class="btn btn-primary">Update purchase</button><a data-ajax-page
                                class="btn btn-light" href="{{ route('purchases.show', $purchase) }}">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        (() => {
            document.querySelectorAll('.js-quantity,.js-cost').forEach(input => input.addEventListener('input',
                function() {
                    const row = this.closest('tr'),
                        quantity = Number(row.querySelector('.js-quantity').value) || 0,
                        cost = Number(row.querySelector('.js-cost').value) || 0;
                    row.querySelector('.js-line-total').textContent = '৳' + (quantity * cost).toLocaleString(
                        'en-BD', {
                            minimumFractionDigits: 2
                        });
                }));
        })();
    </script>
@endsection
