@php
    $currentAdmin = Auth::guard('admin')->user();
    $canAdd = $currentAdmin?->hasModuleAccess('shipping_method', 'add');
    $canEdit = $currentAdmin?->hasModuleAccess('shipping_method', 'edit');
    $canDelete = $currentAdmin?->hasModuleAccess('shipping_method', 'delete');
@endphp
<div class="app-content main-content"><div class="side-app"><div class="container-fluid main-container">
    <div class="page-header"><div class="page-leftheader"><h4 class="page-title">{{ $title }}</h4></div></div>
    <div class="row"><div class="col-12"><div class="card">
        <div class="card-header justify-content-between"><div class="card-title">{{ $title }}</div>
            @if($canAdd)<button type="button" class="btn btn-info" data-crud-create data-crud-modal="#shipping-method-modal" data-store-url="{{ route('admin-shipping-method.store') }}" data-create-title="Add Shipping Method">Add Shipping Method</button>@endif
        </div>
        <div class="card-body">
            <div class="mb-3 d-flex align-items-center gap-2"><label class="mb-0">Show</label><select class="form-select form-select-sm w-auto" data-server-per-page>@foreach([10,20,50,100] as $size)<option value="{{ $size }}" {{ (int)request('per_page',10)===$size?'selected':'' }}>{{ $size }}</option>@endforeach</select><span>entries</span></div>
            <div class="table-responsive"><table data-server-pagination class="table table-bordered text-nowrap"><thead><tr><th>ID</th><th>Name</th><th>Code</th><th>Charge</th><th>Delivery time</th><th>Position</th><th>Status</th><th>Action</th></tr></thead><tbody>
            @foreach($shippingMethods as $method)<tr><td>{{ $method->id }}</td><td>{{ $method->name }}</td><td>{{ $method->code }}</td><td>৳{{ number_format((float)$method->charge,2) }}</td><td>{{ $method->delivery_time ?: '-' }}</td><td>{{ $method->position }}</td><td>
                @if($canEdit)<button type="button" class="btn btn-sm {{ $method->status?'btn-success':'btn-secondary' }}" data-crud-status data-url="{{ route('admin-shipping-method.status',$method) }}">{{ $method->status?'Active':'Inactive' }}</button>@else {{ $method->status?'Active':'Inactive' }} @endif
            </td><td>@if($canEdit)<button type="button" class="btn btn-sm btn-primary" data-crud-edit data-crud-modal="#shipping-method-modal" data-url="{{ route('admin-shipping-method.show',$method) }}" data-update-url="{{ route('admin-shipping-method.update',$method) }}">Edit</button>@endif @if($canDelete)<button type="button" class="btn btn-sm btn-danger" data-crud-delete data-url="{{ route('admin-shipping-method.delete',$method) }}">Delete</button>@endif</td></tr>@endforeach
            </tbody></table></div><div class="mt-3">{{ $shippingMethods->links() }}</div>
        </div>
    </div></div></div>
</div></div></div>

<div class="modal fade" id="shipping-method-modal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><form data-crud-form>@csrf
    <div class="modal-header"><h5 class="modal-title" data-crud-title>Add Shipping Method</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="alert alert-danger d-none js-crud-errors"></div><div class="row">
        <div class="col-md-6 mb-3"><label class="form-label">Name</label><input name="name" class="form-control" maxlength="100" required></div>
        <div class="col-md-6 mb-3"><label class="form-label">Code</label><input name="code" class="form-control" maxlength="50" placeholder="standard_delivery" required></div>
        <div class="col-md-6 mb-3"><label class="form-label">Charge</label><input name="charge" type="number" min="0" step="0.01" class="form-control" required></div>
        <div class="col-md-6 mb-3"><label class="form-label">Delivery time</label><input name="delivery_time" class="form-control" maxlength="100" placeholder="2-4 business days"></div>
        <div class="col-md-6 mb-3"><label class="form-label">Bootstrap icon class</label><input name="icon" class="form-control" maxlength="100" placeholder="bi bi-truck"></div>
        <div class="col-md-3 mb-3"><label class="form-label">Position</label><input name="position" type="number" min="0" value="0" class="form-control" required></div>
        <div class="col-md-3 mb-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="1">Active</option><option value="0">Inactive</option></select></div>
        <div class="col-12"><label class="form-label">Description</label><input name="description" class="form-control" maxlength="255"></div>
    </div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary" data-crud-submit>Save Method</button></div>
</form></div></div></div>
