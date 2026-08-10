@php
    $currentAdmin = Auth::guard('admin')->user();
    $canAddProducts = $currentAdmin?->hasModuleAccess('product', 'add');
    $canEditProducts = $currentAdmin?->hasModuleAccess('product', 'edit');
    $canDeleteProducts = $currentAdmin?->hasModuleAccess('product', 'delete');
@endphp
<style>
    #product-form-modal { padding: 0 !important; }
    #product-form-modal .product-modal-dialog {
        width: min(1180px, calc(100vw - 32px));
        max-width: 1180px !important;
        height: calc(100vh - 40px);
        margin: 20px auto !important;
    }
    #product-form-modal .modal-content {
        height: 100%;
        max-height: 100%;
        overflow: hidden;
        border: 0;
        border-radius: 14px;
        box-shadow: 0 24px 70px rgba(0, 0, 0, .28);
    }
    #product-form-modal form { display: flex; flex-direction: column; min-height: 0; height: 100%; }
    #product-form-modal .modal-header,
    #product-form-modal .modal-footer { flex: 0 0 auto; }
    #product-form-modal .modal-header { padding: 18px 24px; align-items: center; }
    #product-form-modal .product-modal-heading { display: flex; align-items: center; gap: 12px; }
    #product-form-modal .product-modal-icon {
        display: inline-flex; align-items: center; justify-content: center;
        width: 42px; height: 42px; border-radius: 10px;
        color: #fff; background: linear-gradient(135deg, #467fcf, #6c5ce7);
        font-size: 19px;
    }
    #product-form-modal .product-modal-subtitle { margin: 2px 0 0; font-size: 12px; opacity: .65; }
    #product-form-modal .modal-body { flex: 1 1 auto; min-height: 0; overflow-y: auto; padding: 22px 24px; }
    #product-form-modal .product-form-section {
        padding: 20px; margin-bottom: 20px; border: 1px solid rgba(128, 128, 128, .18);
        border-radius: 12px; background: rgba(128, 128, 128, .035);
    }
    #product-form-modal .product-section-title {
        display: flex; align-items: center; gap: 9px; margin: 0 0 18px;
        font-size: 15px; font-weight: 700;
    }
    #product-form-modal .product-section-title i { color: #467fcf; font-size: 18px; }
    #product-form-modal .form-label { margin-bottom: 7px; font-size: 12px; font-weight: 600; }
    #product-form-modal .form-control, #product-form-modal .form-select { min-height: 42px; border-radius: 7px; }
    #product-form-modal textarea.form-control { min-height: auto; }
    #product-form-modal .modal-footer { padding: 14px 24px; background: inherit; box-shadow: 0 -8px 22px rgba(0, 0, 0, .035); }
    #product-form-modal .modal-footer .btn { min-width: 110px; padding: 9px 20px; border-radius: 7px; }
    #product-form-modal .modal-footer .btn-primary { box-shadow: 0 5px 14px rgba(70, 127, 207, .3); }
    @media (max-width: 767.98px) {
        #product-form-modal .product-modal-dialog { width: calc(100vw - 16px); height: calc(100vh - 16px); margin: 8px auto !important; }
        #product-form-modal .modal-header, #product-form-modal .modal-body, #product-form-modal .modal-footer { padding-left: 15px; padding-right: 15px; }
        #product-form-modal .product-form-section { padding: 15px; }
        #product-form-modal .modal-footer .btn { flex: 1; }
    }
</style>
<div class="app-content main-content"><div class="side-app"><div class="container-fluid main-container">
    <div class="page-header"><div class="page-leftheader"><h4 class="page-title">{{ $title }}</h4></div></div>
    <div class="row"><div class="col-12"><div class="card">
        <div class="card-header justify-content-between"><div class="card-title">{{ $title }}</div>
            @if ($canAddProducts)<button type="button" class="btn btn-info" data-crud-create data-crud-modal="#product-form-modal" data-store-url="{{ route('admin-product.store') }}" data-create-title="Add Product">Add Product</button>@endif
        </div>
        <div class="card-body">
            <div class="mb-3 d-flex align-items-center gap-2"><label class="mb-0">Show</label>
                <select class="form-select form-select-sm w-auto" data-server-per-page>@foreach ([10,20,50,100] as $size)<option value="{{ $size }}" {{ (int) request('per_page',10) === $size ? 'selected' : '' }}>{{ $size }}</option>@endforeach</select><span>entries</span>
                <button class="btn btn-sm btn-success" data-table-export="#products-table" data-table-export-type="excel">Excel</button><button class="btn btn-sm btn-primary" data-table-export="#products-table" data-table-export-type="word">Word</button>
            </div>
            <div class="table-responsive"><table id="products-table" data-server-pagination class="table table-bordered text-nowrap key-buttons">
                <thead><tr><th>ID</th><th>Image</th><th>Product</th><th>Code</th><th>Section</th><th>Category</th><th>Brand</th><th>Color</th><th>Price</th><th>Featured</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>@foreach ($getProducts as $product)<tr>
                    <td>{{ $product['id'] }}</td>
                    <td>@if ($product['product_image'])<img src="{{ asset('admin/productimage/'.basename($product['product_image'])) }}" alt="{{ $product['product_name'] }}" class="rounded border" width="45" height="45" style="object-fit:cover">@else - @endif</td>
                    <td>{{ $product['product_name'] }}</td><td>{{ $product['product_code'] }}</td><td>{{ $product['section_name'] ?? '-' }}</td><td>{{ $product['category_name'] ?? '-' }}</td><td>{{ $product['brand_name'] ?? '-' }}</td>
                    <td>@if ($product['product_color'])<span class="d-inline-block border rounded me-1 align-middle" style="width:20px;height:20px;background:{{ $product['product_color'] }}"></span>{{ $product['product_color'] }}@else - @endif</td>
                    <td>{{ number_format((float) $product['product_price'], 2) }}</td><td>{{ $product['is_featured'] }}</td>
                    <td>@if ($canEditProducts)<button type="button" class="btn btn-sm {{ $product['status'] ? 'btn-success' : 'btn-secondary' }}" data-crud-status data-url="{{ route('admin-product.status',$product['id']) }}">{{ $product['status'] ? 'Active' : 'Inactive' }}</button>@else {{ $product['status'] ? 'Active' : 'Inactive' }} @endif</td>
                    <td>@if ($canEditProducts)<button type="button" class="btn btn-sm btn-primary" data-crud-edit data-crud-modal="#product-form-modal" data-url="{{ route('admin-product.show',$product['id']) }}" data-update-url="{{ route('admin-product.update',$product['id']) }}">Edit</button>@endif
                        @if ($canDeleteProducts)<button type="button" class="btn btn-sm btn-danger" data-crud-delete data-url="{{ route('admin-product.delete',$product['id']) }}">Delete</button>@endif</td>
                </tr>@endforeach</tbody>
            </table></div><div class="mt-3">{{ $getProducts->links() }}</div>
        </div>
    </div></div></div>
</div></div></div>

<div class="modal fade" id="product-form-modal" tabindex="-1" aria-hidden="true"><div class="modal-dialog product-modal-dialog"><div class="modal-content">
<form data-crud-form enctype="multipart/form-data"><div class="modal-header"><div class="product-modal-heading"><span class="product-modal-icon"><i class="fe fe-shopping-bag"></i></span><div><h5 class="modal-title" data-crud-title>Add Product</h5><p class="product-modal-subtitle">Manage product details, pricing, media and SEO</p></div></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
<div class="modal-body"><div class="alert alert-danger d-none js-crud-errors"></div>@csrf
    <div class="product-form-section"><h6 class="product-section-title"><i class="fe fe-box"></i> Basic Information</h6><div class="row">
        <input type="hidden" name="section_id">
        <div class="col-md-8 mb-3">
            <label class="form-label">Select Category *</label>
            <select id="product-category-select" name="category_id" class="form-select" data-section-source="[name='section_id']" required>
                <option value="">Select Category</option>
                @foreach ($sections as $section)
                    <optgroup label="{{ $section->name }}">
                        @foreach ($categoryGroups as $group)
                            @if ($group['section_id'] === (int) $section->id)
                                <option value="{{ $group['root_id'] }}" data-section-id="{{ $group['section_id'] }}">
                                    {{ $group['root_name'] }} (Main Category)
                                </option>
                                @foreach ($group['options'] as $option)
                                    <option value="{{ $option['id'] }}" data-section-id="{{ $group['section_id'] }}">
                                        {{ str_repeat('    ', $option['depth']) }}» {{ $option['name'] }}
                                    </option>
                                @endforeach
                            @endif
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            <small class="text-muted">Sections are bold group headings; their categories and subcategories appear underneath.</small>
        </div>
        <div class="col-md-4 mb-3"><label class="form-label">Brand</label><select name="brand_id" class="form-select"><option value="">No Brand</option>@foreach ($brands as $brand)<option value="{{ $brand->id }}">{{ $brand->name }}</option>@endforeach</select></div>
        <div class="col-md-6 mb-3"><label class="form-label">Product Name *</label><input name="product_name" class="form-control" maxlength="255" required></div>
        <div class="col-md-3 mb-3"><label class="form-label">Product Code *</label><input name="product_code" class="form-control" maxlength="100" required></div>
        <div class="col-md-3 mb-3"><label class="form-label">Color</label><select name="product_color" class="form-select"><option value="">No Color</option>@foreach ($colors as $color)<option value="{{ $color->color_code }}">{{ $color->name }} ({{ $color->color_code }})</option>@endforeach</select></div>
        <div class="col-md-4 mb-3"><label class="form-label">Price *</label><input type="number" name="product_price" class="form-control" step="0.01" min="0" required></div>
        <div class="col-md-4 mb-3"><label class="form-label">Discount (%)</label><input type="number" name="product_discount" class="form-control" value="0" step="0.01" min="0" max="100"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Weight</label><input type="number" name="product_weight" class="form-control" step="0.01" min="0"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Product Image</label><input type="file" name="product_image" class="form-control" accept="image/*"><img data-image-preview-for="product_image" class="d-none mt-2 rounded border" width="100" height="100" style="object-fit:cover"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Video URL</label><input type="url" name="product_video" class="form-control" maxlength="255"></div>
        <div class="col-md-2 mb-3"><label class="form-label">Featured *</label><select name="is_featured" class="form-select"><option value="No">No</option><option value="Yes">Yes</option></select></div>
        <div class="col-md-2 mb-3"><label class="form-label">Status *</label><select name="status" class="form-select"><option value="1">Active</option><option value="0">Inactive</option></select></div>
        <div class="col-12 mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="4"></textarea></div>
    </div></div><div class="product-form-section mb-0"><h6 class="product-section-title"><i class="fe fe-search"></i> SEO Information</h6><div class="row">
        <div class="col-md-6 mb-3"><label class="form-label">Meta Title</label><input name="meta_title" class="form-control" maxlength="255"></div>
        <div class="col-md-6 mb-3"><label class="form-label">URL Structure</label><input name="url_structure" class="form-control" maxlength="255"></div>
        <div class="col-md-6 mb-3"><label class="form-label">Meta Description</label><textarea name="meta_description" class="form-control" rows="3"></textarea></div>
        <div class="col-md-6 mb-3"><label class="form-label">Meta Image</label><input type="file" name="meta_image" class="form-control" accept="image/*"><img data-image-preview-for="meta_image" class="d-none mt-2 rounded border" width="100" height="100" style="object-fit:cover"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Heading Tag</label><input name="heading_tag" class="form-control" maxlength="255"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Meta Robot</label><input name="meta_robot" class="form-control" maxlength="255"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Meta Keywords</label><input name="meta_keywords" class="form-control" maxlength="255"></div>
        <div class="col-md-6 mb-3"><label class="form-label">Canonical URL</label><input type="url" name="canonical_tag" class="form-control" maxlength="255"></div>
        <div class="col-md-6 mb-3"><label class="form-label">Schema Markup</label><textarea name="schema_markup" class="form-control" rows="3"></textarea></div>
        <div class="col-12 mb-3"><label class="form-label">Meta Data</label><textarea name="meta_data" class="form-control" rows="3"></textarea></div>
    </div></div>
</div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary" data-crud-submit>Save Product</button></div></form>
</div></div></div>
