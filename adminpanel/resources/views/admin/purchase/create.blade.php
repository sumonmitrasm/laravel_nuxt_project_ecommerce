@extends('admin.layout.layout')
@section('content')
    <div class="app-content main-content">
        <div class="side-app">
            <div class="container-fluid main-container">
                <div class="page-header">
                    <div class="page-leftheader">
                        <h4 class="page-title">Add purchase</h4>
                        <p class="text-muted mb-0">Stock is added immediately after you save this purchase.</p>
                    </div>
                    <div class="page-rightheader"><a data-ajax-page class="btn btn-outline-primary"
                            href="{{ route('purchases.index') }}">Back to purchases</a></div>
                </div>
                @if ($suppliers->isEmpty() || $variants->isEmpty())
                    <div class="alert alert-warning">
                        @if ($suppliers->isEmpty())
                            Add a supplier first.
                            @endif @if ($variants->isEmpty())
                                Add a product variant first.
                            @endif
                    </div>
                @else
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form data-purchase-form method="POST" action="{{ route('purchases.store') }}">@csrf<div
                            class="row">
                            <div class="col-xl-8">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h3 class="card-title">Products received</h3><button id="add-item" type="button"
                                            class="btn btn-sm btn-outline-primary">Add product</button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="ps-3">Product / SKU</th>
                                                    <th width="130">Quantity</th>
                                                    <th width="170">Unit cost</th>
                                                    <th width="140" class="text-end">Total</th>
                                                    <th width="50"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="purchase-items"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Purchase information</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3"><label class="form-label">Supplier *</label><select
                                                class="form-select" name="supplier_id" required>
                                                @foreach ($suppliers as $supplier)
                                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3"><label class="form-label">Purchase date *</label><input
                                                class="form-control" type="date" name="purchase_date"
                                                value="{{ old('purchase_date', now()->toDateString()) }}" required></div>
                                        <div class="mb-3"><label class="form-label">Paid now</label><input
                                                class="form-control" type="number" name="paid_amount" min="0"
                                                step="0.01" value="{{ old('paid_amount', 0) }}"></div>
                                        <div class="mb-3"><label class="form-label">Note</label>
                                            <textarea class="form-control" name="note" rows="3">{{ old('note') }}</textarea>
                                        </div>
                                        <div class="purchase-total"><span>Total purchase cost</span><b
                                                id="purchase-total">৳0.00</b></div><button
                                            class="btn btn-primary w-100 mt-3">Save purchase & add stock</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <template id="item-template">
                        <tr>
                            <td class="ps-3"><select class="form-select variant" name="items[INDEX][variant_id]" required>
                                    <option value="">Select product</option>
                                    @foreach ($variants as $variant)
                                        <option value="{{ $variant->id }}">{{ $variant->product?->product_name }} —
                                            {{ $variant->sku }}</option>
                                    @endforeach
                                </select></td>
                            <td><input class="form-control quantity" type="number" name="items[INDEX][quantity]"
                                    min="1" value="1" required></td>
                            <td><input class="form-control cost" type="number" name="items[INDEX][unit_cost]"
                                    min="0" step="0.01" value="0" required></td>
                            <td class="text-end line-total">৳0.00</td>
                            <td><button class="btn btn-sm btn-light remove-item" type="button" title="Remove">×</button>
                            </td>
                        </tr>
                    </template>
                @endif
            </div>
        </div>
    </div>
    <style>
        .purchase-total {
            display: flex;
            justify-content: space-between;
            padding: 13px;
            background: #f5f7fb;
            border-radius: 6px
        }

        .purchase-total b {
            font-size: 18px
        }

        .remove-item {
            font-size: 22px;
            line-height: 1
        }
    </style>
    <script>
        (() => {
            const list = document.getElementById('purchase-items'),
                template = document.getElementById('item-template'),
                add = document.getElementById('add-item'),
                total = document.getElementById('purchase-total');
            if (!list || !template) return;
            let index = 0;

            function refresh() {
                let amount = 0;
                list.querySelectorAll('tr').forEach(row => {
                    const quantity = Number(row.querySelector('.quantity').value) || 0,
                        cost = Number(row.querySelector('.cost').value) || 0,
                        line = quantity * cost;
                    row.querySelector('.line-total').textContent = '৳' + line.toLocaleString('en-BD', {
                        minimumFractionDigits: 2
                    });
                    amount += line
                });
                total.textContent = '৳' + amount.toLocaleString('en-BD', {
                    minimumFractionDigits: 2
                })
            }

            function addRow() {
                const html = template.innerHTML.replaceAll('INDEX', index++);
                list.insertAdjacentHTML('beforeend', html);
                refresh()
            }
            add?.addEventListener('click', addRow);
            list.addEventListener('input', refresh);
            list.addEventListener('click', event => {
                if (event.target.closest('.remove-item')) {
                    event.target.closest('tr').remove();
                    refresh()
                }
            });
            addRow()
        })();
    </script>
@endsection
