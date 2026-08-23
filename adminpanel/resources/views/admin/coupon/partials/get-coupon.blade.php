@php
    $currentAdmin = Auth::guard('admin')->user();
    $canAddCoupons = $currentAdmin?->hasModuleAccess('coupon', 'add');
    $canEditCoupons = $currentAdmin?->hasModuleAccess('coupon', 'edit');
    $canDeleteCoupons = $currentAdmin?->hasModuleAccess('coupon', 'delete');
@endphp

<style>
    #coupon-form-modal {
        padding: 0 !important;
    }

    #coupon-form-modal .coupon-modal-dialog {
        width: min(1120px, calc(100vw - 32px));
        max-width: 1120px !important;
        height: calc(100vh - 40px);
        margin: 20px auto !important;
    }

    #coupon-form-modal .modal-content {
        height: 100%;
        max-height: 100%;
        overflow: hidden;
        border: 0;
        border-radius: 14px;
        box-shadow: 0 24px 70px rgba(0, 0, 0, .28);
    }

    #coupon-form-modal form {
        display: flex;
        flex-direction: column;
        min-height: 0;
        height: 100%;
    }

    #coupon-form-modal .modal-header,
    #coupon-form-modal .modal-footer {
        flex: 0 0 auto;
    }

    #coupon-form-modal .modal-header {
        padding: 18px 24px;
        align-items: center;
    }

    #coupon-form-modal .coupon-modal-heading {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    #coupon-form-modal .coupon-modal-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        border-radius: 11px;
        color: #fff;
        background: linear-gradient(135deg, #ff6b6b, #f7b731);
        font-size: 20px;
        box-shadow: 0 7px 18px rgba(255, 107, 107, .25);
    }

    #coupon-form-modal .coupon-modal-subtitle {
        margin: 3px 0 0;
        font-size: 12px;
        opacity: .65;
    }

    #coupon-form-modal .modal-body {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        padding: 22px 24px;
    }

    #coupon-form-modal .coupon-form-section {
        padding: 20px;
        margin-bottom: 18px;
        border: 1px solid rgba(128, 128, 128, .18);
        border-radius: 12px;
        background: rgba(128, 128, 128, .035);
    }

    #coupon-form-modal .coupon-section-title {
        display: flex;
        align-items: center;
        gap: 9px;
        margin: 0 0 5px;
        font-size: 15px;
        font-weight: 700;
    }

    #coupon-form-modal .coupon-section-title i {
        color: #4b66d1;
        font-size: 18px;
    }

    #coupon-form-modal .coupon-section-help {
        margin: 0 0 18px;
        font-size: 12px;
        opacity: .62;
    }

    #coupon-form-modal .form-label {
        margin-bottom: 7px;
        font-size: 12px;
        font-weight: 600;
    }

    #coupon-form-modal .form-control,
    #coupon-form-modal .form-select {
        min-height: 42px;
        border-radius: 7px;
    }

    #coupon-form-modal textarea.form-control {
        min-height: auto;
    }

    #coupon-form-modal select[multiple] {
        min-height: 165px;
        padding: 8px;
    }

    #coupon-form-modal select[multiple] option {
        padding: 8px 10px;
        border-radius: 5px;
    }

    #coupon-form-modal .coupon-choice-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }

    #coupon-form-modal .coupon-choice-grid.customer-choices {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    #coupon-form-modal .coupon-choice {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 70px;
        padding: 12px 14px;
        border: 1px solid rgba(128, 128, 128, .22);
        border-radius: 9px;
        color: inherit;
        background: rgba(128, 128, 128, .025);
        text-align: left;
        transition: border-color .18s ease, background .18s ease, transform .18s ease;
    }

    #coupon-form-modal .coupon-choice:hover {
        border-color: rgba(75, 102, 209, .65);
        transform: translateY(-1px);
    }

    #coupon-form-modal .coupon-choice.active {
        border-color: #4b66d1;
        background: rgba(75, 102, 209, .12);
        box-shadow: inset 0 0 0 1px rgba(75, 102, 209, .16);
    }

    #coupon-form-modal .coupon-choice-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        border-radius: 8px;
        color: #4b66d1;
        background: rgba(75, 102, 209, .12);
        font-size: 16px;
    }

    #coupon-form-modal .coupon-choice strong {
        display: block;
        font-size: 12px;
        color: inherit;
    }

    #coupon-form-modal .coupon-choice small {
        display: block;
        margin-top: 2px;
        font-size: 10px;
        opacity: .58;
    }

    #coupon-form-modal .coupon-picker {
        overflow: hidden;
        border: 1px solid rgba(128, 128, 128, .22);
        border-radius: 9px;
        background: rgba(128, 128, 128, .025);
    }

    #coupon-form-modal .coupon-picker-toolbar {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px;
        border-bottom: 1px solid rgba(128, 128, 128, .16);
    }

    #coupon-form-modal .coupon-picker-search {
        min-height: 36px;
    }

    #coupon-form-modal .coupon-picker-count {
        white-space: nowrap;
        font-size: 11px;
        opacity: .68;
    }

    #coupon-form-modal .coupon-picker-clear {
        padding: 0;
        border: 0;
        color: #ff5b5b;
        background: transparent;
        white-space: nowrap;
        font-size: 11px;
        font-weight: 600;
    }

    #coupon-form-modal .coupon-picker-options {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 6px;
        max-height: 220px;
        padding: 10px;
        overflow-y: auto;
    }

    #coupon-form-modal .coupon-picker-option {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 0;
        margin: 0;
        padding: 9px 10px;
        border: 1px solid rgba(128, 128, 128, .14);
        border-radius: 7px;
        cursor: pointer;
    }

    #coupon-form-modal .coupon-picker-option:hover,
    #coupon-form-modal .coupon-picker-option.selected {
        border-color: rgba(75, 102, 209, .55);
        background: rgba(75, 102, 209, .09);
    }

    #coupon-form-modal .coupon-picker-option input {
        flex: 0 0 auto;
    }

    #coupon-form-modal .coupon-picker-label {
        min-width: 0;
    }

    #coupon-form-modal .coupon-picker-label strong,
    #coupon-form-modal .coupon-picker-label small {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    #coupon-form-modal .coupon-picker-label strong {
        font-size: 12px;
    }

    #coupon-form-modal .coupon-picker-label small {
        margin-top: 2px;
        font-size: 10px;
        opacity: .58;
    }

    #coupon-form-modal .coupon-category-badge {
        margin-left: auto;
        padding: 3px 6px;
        border-radius: 10px;
        background: rgba(128, 128, 128, .13);
        white-space: nowrap;
        font-size: 9px;
    }

    #coupon-form-modal [data-customer-target]>select[multiple],
    #coupon-form-modal [data-customer-target]>select[multiple]+small {
        display: none !important;
    }

    #coupon-form-modal .modal-footer {
        padding: 14px 24px;
        background: inherit;
        box-shadow: 0 -8px 22px rgba(0, 0, 0, .04);
    }

    #coupon-form-modal .modal-footer .btn {
        min-width: 115px;
        padding: 9px 20px;
        border-radius: 7px;
    }

    #coupon-form-modal .modal-footer .btn-primary {
        box-shadow: 0 5px 14px rgba(70, 91, 207, .3);
    }

    @media (max-width: 767.98px) {
        #coupon-form-modal .coupon-modal-dialog {
            width: calc(100vw - 16px);
            height: calc(100vh - 16px);
            margin: 8px auto !important;
        }

        #coupon-form-modal .modal-header,
        #coupon-form-modal .modal-body,
        #coupon-form-modal .modal-footer {
            padding-left: 15px;
            padding-right: 15px;
        }

        #coupon-form-modal .coupon-form-section {
            padding: 15px;
        }

        #coupon-form-modal .modal-footer .btn {
            flex: 1;
        }

        #coupon-form-modal .coupon-choice-grid,
        #coupon-form-modal .coupon-choice-grid.customer-choices {
            grid-template-columns: 1fr;
        }

        #coupon-form-modal .coupon-picker-toolbar {
            align-items: stretch;
            flex-wrap: wrap;
        }

        #coupon-form-modal .coupon-picker-search {
            width: 100%;
        }

        #coupon-form-modal .coupon-picker-options {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="app-content main-content">
    <div class="side-app">
        <div class="container-fluid main-container">
            <div class="page-header">
                <div class="page-leftheader">
                    <h4 class="page-title">{{ $title }}</h4>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header justify-content-between">
                            <div class="card-title">Coupon Management</div>
                            @if ($canAddCoupons)
                                <button type="button" class="btn btn-info" data-crud-create
                                    data-crud-modal="#coupon-form-modal"
                                    data-store-url="{{ route('admin-coupon.store') }}"
                                    data-create-title="Add Coupon">Add Coupon</button>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="mb-3 d-flex align-items-center gap-2">
                                <label class="mb-0">Show</label>
                                <select class="form-select form-select-sm w-auto" data-server-per-page>
                                    @foreach ([10, 20, 50, 100] as $size)
                                        <option value="{{ $size }}"
                                            {{ (int) request('per_page', 10) === $size ? 'selected' : '' }}>
                                            {{ $size }}</option>
                                    @endforeach
                                </select>
                                <span>entries</span>
                                <button class="btn btn-sm btn-success" data-table-export="#coupons-table"
                                    data-table-export-type="excel">Excel</button>
                                <button class="btn btn-sm btn-primary" data-table-export="#coupons-table"
                                    data-table-export-type="word">Word</button>
                            </div>
                            <div class="table-responsive">
                                <table id="coupons-table" data-server-pagination
                                    class="table table-bordered text-nowrap key-buttons">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Code</th>
                                            <th>Discount</th>
                                            <th>Scope</th>
                                            <th>Valid Until</th>
                                            <th>Created By</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($getCoupons as $coupon)
                                            <tr>
                                                <td>{{ $coupon->id }}</td>
                                                <td>{{ $coupon->name }}</td>
                                                <td><strong>{{ $coupon->code }}</strong></td>
                                                <td>
                                                    @if ($coupon->discount_type === 'percentage')
                                                        {{ $coupon->discount_value }}%
                                                    @elseif ($coupon->discount_type === 'fixed')
                                                        ৳{{ number_format((float) $coupon->discount_value, 2) }}
                                                    @else
                                                        Free shipping
                                                    @endif
                                                </td>
                                                <td>{{ ucfirst($coupon->scope) }}</td>
                                                <td>{{ $coupon->expires_at?->format('d M Y, h:i A') ?? 'No expiry' }}
                                                </td>
                                                <td>{{ $coupon->createdBy?->name ?? 'Unknown' }}<small
                                                        class="d-block text-muted">{{ ucfirst($coupon->createdBy?->type ?? '') }}</small>
                                                </td>
                                                <td>
                                                    @if ($canEditCoupons)
                                                        <button type="button"
                                                            class="btn btn-sm {{ $coupon->is_active ? 'btn-success' : 'btn-secondary' }}"
                                                            data-crud-status
                                                            data-url="{{ route('admin-coupon.status', $coupon) }}">{{ $coupon->is_active ? 'Active' : 'Inactive' }}</button>
                                                    @else
                                                        {{ $coupon->is_active ? 'Active' : 'Inactive' }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($canEditCoupons)
                                                        <button type="button" class="btn btn-sm btn-primary"
                                                            data-crud-edit data-crud-modal="#coupon-form-modal"
                                                            data-url="{{ route('admin-coupon.show', $coupon) }}"
                                                            data-update-url="{{ route('admin-coupon.update', $coupon) }}">Edit</button>
                                                    @endif
                                                    @if ($canDeleteCoupons)
                                                        <button type="button" class="btn btn-sm btn-danger"
                                                            data-crud-delete
                                                            data-url="{{ route('admin-coupon.delete', $coupon) }}">Delete</button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center text-muted">No coupons found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">{{ $getCoupons->links() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="coupon-form-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog coupon-modal-dialog">
        <div class="modal-content">
            <form data-crud-form>
                @csrf
                <div class="modal-header">
                    <div class="coupon-modal-heading">
                        <span class="coupon-modal-icon"><i class="fe fe-tag"></i></span>
                        <div>
                            <h5 class="modal-title" data-crud-title>Add Coupon</h5>
                            <p class="coupon-modal-subtitle">Create discounts, set eligibility and control campaign
                                usage</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger d-none js-crud-errors"></div>

                    <div class="coupon-form-section">
                        <h6 class="coupon-section-title"><i class="fe fe-file-text"></i> Basic Information</h6>
                        <p class="coupon-section-help">Give the campaign a clear internal name and a customer-facing
                            coupon code.</p>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Coupon Name *</label><input type="text"
                                    name="name" class="form-control" required maxlength="255"></div>
                            <div class="col-md-6"><label class="form-label">Coupon Code *</label><input type="text"
                                    name="code" class="form-control text-uppercase" required maxlength="100"
                                    placeholder="SAVE20"></div>
                            <input type="hidden" name="apply_method" value="code">
                            <div class="col-12"><label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2" maxlength="2000"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="coupon-form-section">
                        <h6 class="coupon-section-title"><i class="fe fe-percent"></i> Discount Rules</h6>
                        <p class="coupon-section-help">Choose the benefit and protect profitability with order and usage
                            limits.</p>
                        <div class="row g-3">
                            <div class="col-md-4"><label class="form-label">Discount Type *</label><select
                                    name="discount_type" class="form-select" data-coupon-discount-type required>
                                    <option value="percentage">Percentage</option>
                                    <option value="fixed">Fixed Amount</option>
                                    <option value="free_shipping">Free Shipping</option>
                                </select></div>
                            <div class="col-md-4" data-coupon-discount-value><label class="form-label">Discount Value
                                    *</label><input type="number" name="discount_value" class="form-control"
                                    value="0" min="0" step="0.01" required></div>
                            <div class="col-md-4" data-coupon-maximum><label class="form-label">Maximum
                                    Discount</label><input type="number" name="maximum_discount"
                                    class="form-control" min="0" step="0.01"><small
                                    class="text-muted">Only for percentage coupons.</small></div>
                            <div class="col-md-4"><label class="form-label">Minimum Order Amount</label><input
                                    type="number" name="minimum_order_amount" class="form-control" value="0"
                                    min="0" step="0.01" required><small class="text-muted">Use 0 when no
                                    minimum cart total is required.</small></div>
                            <div class="col-md-4"><label class="form-label">Total Usage Limit</label><input
                                    type="number" name="usage_limit" class="form-control" min="1"
                                    placeholder="Unlimited"></div>
                            <div class="col-md-4"><label class="form-label">Usage Limit Per User</label><input
                                    type="number" name="usage_limit_per_user" class="form-control" min="1"
                                    placeholder="Unlimited"></div>
                            <div class="col-md-6"><label class="form-label">Already Discounted Products</label><select
                                    name="exclude_discounted_products" class="form-select">
                                    <option value="0">Coupon can apply</option>
                                    <option value="1">Exclude these products</option>
                                </select></div>
                        </div>
                    </div>

                    <div class="coupon-form-section">
                        <h6 class="coupon-section-title"><i class="fe fe-target"></i> Coupon Scope</h6>
                        <p class="coupon-section-help">Choose eligible products separately from the customers who may
                            use this coupon.</p>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Product Eligibility *</label>
                                <select name="scope" class="d-none" data-coupon-scope required>
                                    <option value="all">All Products</option>
                                    <option value="products">Selected Products</option>
                                    <option value="categories">Selected Categories</option>
                                    <option value="brands">Selected Brands</option>
                                </select>
                                <div class="coupon-choice-grid">
                                    <button type="button" class="coupon-choice" data-coupon-scope-option="all"><span
                                            class="coupon-choice-icon"><i
                                                class="fe fe-grid"></i></span><span><strong>All
                                                Products</strong><small>Store-wide coupon</small></span></button>
                                    <button type="button" class="coupon-choice"
                                        data-coupon-scope-option="products"><span class="coupon-choice-icon"><i
                                                class="fe fe-box"></i></span><span><strong>Selected
                                                Products</strong><small>Choose individual items</small></span></button>
                                    <button type="button" class="coupon-choice"
                                        data-coupon-scope-option="categories"><span class="coupon-choice-icon"><i
                                                class="fe fe-layers"></i></span><span><strong>Selected
                                                Categories</strong><small>Apply by category</small></span></button>
                                    <button type="button" class="coupon-choice"
                                        data-coupon-scope-option="brands"><span class="coupon-choice-icon"><i
                                                class="fe fe-award"></i></span><span><strong>Selected
                                                Brands</strong><small>Apply by brand</small></span></button>
                                </div>
                            </div>
                            <div class="col-12 mt-2">
                                <label class="form-label">Customer Eligibility *</label>
                                <select name="customer_scope" class="d-none" data-customer-scope required>
                                    <option value="all">All Customers</option>
                                    <option value="selected">Selected Customers</option>
                                    <option value="first_order">First Order Customers</option>
                                    <option value="lifetime_spend">Lifetime Shopping</option>
                                </select>
                                <div class="coupon-choice-grid customer-choices">
                                    <button type="button" class="coupon-choice"
                                        data-customer-scope-option="all"><span class="coupon-choice-icon"><i
                                                class="fe fe-users"></i></span><span><strong>All
                                                Customers</strong><small>Anyone may use this
                                                coupon</small></span></button>
                                    <button type="button" class="coupon-choice"
                                        data-customer-scope-option="selected"><span class="coupon-choice-icon"><i
                                                class="fe fe-user-check"></i></span><span><strong>Selected
                                                Customers</strong><small>Restrict to chosen
                                                accounts</small></span></button>
                                    <button type="button" class="coupon-choice"
                                        data-customer-scope-option="first_order"><span class="coupon-choice-icon"><i
                                                class="fe fe-user-plus"></i></span><span><strong>First
                                                Order</strong><small>Customers with no previous confirmed
                                                order</small></span></button>
                                    <button type="button" class="coupon-choice"
                                        data-customer-scope-option="lifetime_spend"><span
                                            class="coupon-choice-icon"><i
                                                class="fe fe-trending-up"></i></span><span><strong>Lifetime
                                                Shopping</strong><small>Require a minimum completed purchase
                                                total</small></span></button>
                                </div>
                            </div>
                            <div class="col-12 d-none" data-customer-target="selected"><label
                                    class="form-label">Customers *</label><select name="user_ids[]"
                                    class="form-select" multiple size="7">
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} —
                                            {{ $user->email }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Use Ctrl/Cmd to select multiple customers.</small>
                            </div>
                            <div class="col-12 d-none" data-picker-panel="customers">
                                <div class="coupon-picker">
                                    <div class="coupon-picker-toolbar"><input type="search"
                                            class="form-control coupon-picker-search"
                                            placeholder="Search customers..." data-picker-search><span
                                            class="coupon-picker-count" data-picker-count>0 selected</span><button
                                            type="button" class="coupon-picker-clear" data-picker-clear>Clear
                                            all</button></div>
                                    <div class="coupon-picker-options">
                                        @foreach ($users as $user)
                                            <label class="coupon-picker-option" data-picker-option
                                                data-search="{{ strtolower($user->name . ' ' . $user->email) }}"><input
                                                    type="checkbox" value="{{ $user->id }}"
                                                    data-picker-checkbox><span
                                                    class="coupon-picker-label"><strong>{{ $user->name }}</strong><small>{{ $user->email }}</small></span></label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 d-none" data-customer-target="lifetime_spend">
                                <div class="coupon-picker p-3">
                                    <div class="row align-items-end g-3">
                                        <div class="col-md-6"><label class="form-label">Minimum Lifetime Shopping (৳)
                                                *</label><input type="number" name="minimum_lifetime_spend"
                                                class="form-control" min="0.01" step="0.01"
                                                placeholder="5000"></div>
                                        <div class="col-md-6">
                                            <p class="coupon-section-help mb-2">The customer’s completed orders must
                                                total at least this amount. Cancelled, returned and unpaid orders should
                                                not count.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 d-none" data-coupon-target="products">
                                <label class="form-label">Products *</label>
                                <select name="product_ids[]" class="d-none" multiple data-picker-source>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                                    @endforeach
                                </select>
                                <div class="coupon-picker">
                                    <div class="coupon-picker-toolbar"><input type="search"
                                            class="form-control coupon-picker-search" placeholder="Search products..."
                                            data-picker-search><span class="coupon-picker-count" data-picker-count>0
                                            selected</span><button type="button" class="coupon-picker-clear"
                                            data-picker-clear>Clear all</button></div>
                                    <div class="coupon-picker-options">
                                        @foreach ($products as $product)
                                            <label class="coupon-picker-option" data-picker-option
                                                data-search="{{ strtolower($product->product_name) }}"><input
                                                    type="checkbox" value="{{ $product->id }}"
                                                    data-picker-checkbox><span
                                                    class="coupon-picker-label"><strong>{{ $product->product_name }}</strong><small>Product
                                                        #{{ $product->id }}</small></span></label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 d-none" data-coupon-target="categories">
                                <label class="form-label">Categories *</label>
                                <select name="category_ids[]" class="d-none" multiple data-picker-source>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category['id'] }}">{{ $category['path'] }}</option>
                                    @endforeach
                                </select>
                                <div class="coupon-picker">
                                    <div class="coupon-picker-toolbar"><input type="search"
                                            class="form-control coupon-picker-search"
                                            placeholder="Search section or category..." data-picker-search><span
                                            class="coupon-picker-count" data-picker-count>0 selected</span><button
                                            type="button" class="coupon-picker-clear" data-picker-clear>Clear
                                            all</button></div>
                                    <div class="coupon-picker-options">
                                        @foreach ($categories as $category)
                                            <label class="coupon-picker-option" data-picker-option
                                                data-search="{{ strtolower($category['section'] . ' ' . $category['path']) }}"><input
                                                    type="checkbox" value="{{ $category['id'] }}"
                                                    data-picker-checkbox><span
                                                    class="coupon-picker-label"><strong>{{ $category['path'] }}</strong><small>Section:
                                                        {{ $category['section'] }}</small></span><span
                                                    class="coupon-category-badge">{{ $category['depth'] === 0 ? 'Main' : 'Child' }}</span></label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 d-none" data-coupon-target="brands">
                                <label class="form-label">Brands *</label>
                                <select name="brand_ids[]" class="d-none" multiple data-picker-source>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                                <div class="coupon-picker">
                                    <div class="coupon-picker-toolbar"><input type="search"
                                            class="form-control coupon-picker-search" placeholder="Search brands..."
                                            data-picker-search><span class="coupon-picker-count" data-picker-count>0
                                            selected</span><button type="button" class="coupon-picker-clear"
                                            data-picker-clear>Clear all</button></div>
                                    <div class="coupon-picker-options">
                                        @foreach ($brands as $brand)
                                            <label class="coupon-picker-option" data-picker-option
                                                data-search="{{ strtolower($brand->name) }}"><input type="checkbox"
                                                    value="{{ $brand->id }}" data-picker-checkbox><span
                                                    class="coupon-picker-label"><strong>{{ $brand->name }}</strong><small>Brand</small></span></label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="coupon-form-section mb-0">
                        <h6 class="coupon-section-title"><i class="fe fe-calendar"></i> Schedule &amp; Status</h6>
                        <p class="coupon-section-help">Leave dates empty for an immediately available coupon with no
                            expiry.</p>
                        <div class="row g-3">
                            <div class="col-md-4"><label class="form-label">Starts At</label><input
                                    type="datetime-local" name="starts_at" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label">Expires At</label><input
                                    type="datetime-local" name="expires_at" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label">Status</label><select name="is_active"
                                    class="form-select">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"
                        data-crud-submit>Save Coupon</button></div>
            </form>
        </div>
    </div>
</div>

<script>
    (function() {
        if (window.couponFormEventController) window.couponFormEventController.abort();
        window.couponFormEventController = new AbortController();
        const signal = window.couponFormEventController.signal;
        const modal = document.getElementById('coupon-form-modal');
        if (!modal) return;

        const pickerSource = panel => panel.dataset.pickerPanel === 'customers' ?
            modal.querySelector('[data-customer-target="selected"] select[multiple]') :
            panel.querySelector('[data-picker-source]');

        const syncPicker = panel => {
            const source = pickerSource(panel);
            if (!source) return;
            const selected = new Set(Array.from(source.selectedOptions, option => option.value));
            panel.querySelectorAll('[data-picker-checkbox]').forEach(checkbox => {
                checkbox.checked = selected.has(checkbox.value);
                checkbox.closest('[data-picker-option]')?.classList.toggle('selected', checkbox
                .checked);
            });
            const count = panel.querySelector('[data-picker-count]');
            if (count) count.textContent = `${selected.size} selected`;
        };

        const syncPickerSource = panel => {
            const source = pickerSource(panel);
            if (!source) return;
            const selected = new Set(Array.from(panel.querySelectorAll('[data-picker-checkbox]:checked'),
                checkbox => checkbox.value));
            Array.from(source.options).forEach(option => option.selected = selected.has(option.value));
            syncPicker(panel);
        };

        const refreshCouponFields = () => {
            const scope = modal.querySelector('[data-coupon-scope]')?.value || 'all';
            modal.querySelectorAll('[data-coupon-target]').forEach(element => element.classList.toggle('d-none',
                element.dataset.couponTarget !== scope));
            modal.querySelectorAll('[data-coupon-scope-option]').forEach(element => {
                const active = element.dataset.couponScopeOption === scope;
                element.classList.toggle('active', active);
                element.setAttribute('aria-pressed', active ? 'true' : 'false');
            });

            const customerScope = modal.querySelector('[data-customer-scope]')?.value || 'all';
            modal.querySelectorAll('[data-customer-target]').forEach(element => element.classList.toggle(
                'd-none', element.dataset.customerTarget !== customerScope));
            modal.querySelectorAll('[data-picker-panel="customers"]').forEach(element => element.classList
                .toggle('d-none', customerScope !== 'selected'));
            modal.querySelectorAll('[data-customer-scope-option]').forEach(element => {
                const active = element.dataset.customerScopeOption === customerScope;
                element.classList.toggle('active', active);
                element.setAttribute('aria-pressed', active ? 'true' : 'false');
            });

            const type = modal.querySelector('[data-coupon-discount-type]')?.value || 'percentage';
            modal.querySelector('[data-coupon-discount-value]')?.classList.toggle('d-none', type ===
                'free_shipping');
            modal.querySelector('[data-coupon-maximum]')?.classList.toggle('d-none', type !== 'percentage');
            modal.querySelectorAll('[data-coupon-target], [data-picker-panel]').forEach(syncPicker);
        };

        modal.addEventListener('shown.bs.modal', refreshCouponFields, {
            signal
        });
        modal.addEventListener('change', event => {
            if (event.target.matches(
                    '[data-coupon-scope], [data-customer-scope], [data-coupon-discount-type]'))
                refreshCouponFields();
            if (event.target.matches('[data-picker-checkbox]')) {
                const panel = event.target.closest('[data-coupon-target], [data-picker-panel]');
                if (panel) syncPickerSource(panel);
            }
        }, {
            signal
        });
        modal.addEventListener('input', event => {
            if (!event.target.matches('[data-picker-search]')) return;
            const query = event.target.value.trim().toLowerCase();
            const panel = event.target.closest('[data-coupon-target], [data-picker-panel]');
            panel?.querySelectorAll('[data-picker-option]').forEach(option => {
                option.classList.toggle('d-none', query !== '' && !option.dataset.search.includes(
                    query));
            });
        }, {
            signal
        });
        modal.addEventListener('click', event => {
            const productChoice = event.target.closest('[data-coupon-scope-option]');
            if (productChoice) {
                modal.querySelector('[data-coupon-scope]').value = productChoice.dataset.couponScopeOption;
                refreshCouponFields();
            }
            const customerChoice = event.target.closest('[data-customer-scope-option]');
            if (customerChoice) {
                modal.querySelector('[data-customer-scope]').value = customerChoice.dataset
                    .customerScopeOption;
                refreshCouponFields();
            }
            const clearButton = event.target.closest('[data-picker-clear]');
            if (clearButton) {
                const panel = clearButton.closest('[data-coupon-target], [data-picker-panel]');
                panel?.querySelectorAll('[data-picker-checkbox]').forEach(checkbox => checkbox.checked =
                    false);
                if (panel) syncPickerSource(panel);
            }
        }, {
            signal
        });
    })();
</script>
