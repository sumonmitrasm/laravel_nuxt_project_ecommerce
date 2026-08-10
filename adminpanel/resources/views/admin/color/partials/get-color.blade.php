@php
    $currentAdmin = Auth::guard('admin')->user();
    $canAddColors = $currentAdmin?->hasModuleAccess('color', 'add');
    $canEditColors = $currentAdmin?->hasModuleAccess('color', 'edit');
    $canDeleteColors = $currentAdmin?->hasModuleAccess('color', 'delete');
@endphp
<div class="app-content main-content">
    <div class="side-app">
        <div class="container-fluid main-container">
            <div class="page-header"><div class="page-leftheader"><h4 class="page-title">{{ $title }}</h4></div></div>
            <div class="row"><div class="col-12"><div class="card">
                <div class="card-header justify-content-between">
                    <div class="card-title">{{ $title }}</div>
                    @if ($canAddColors)
                        <button type="button" class="btn btn-info" data-crud-create data-crud-modal="#color-form-modal"
                            data-store-url="{{ route('admin-color.store') }}" data-create-title="Add Color">Add Color</button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="mb-3 d-flex align-items-center gap-2">
                        <label class="mb-0">Show</label>
                        <select class="form-select form-select-sm w-auto" data-server-per-page>
                            @foreach ([10, 20, 50, 100] as $size)
                                <option value="{{ $size }}" {{ (int) request('per_page', 10) === $size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                        <span>entries</span>
                        <button class="btn btn-sm btn-success" data-table-export="#colors-table" data-table-export-type="excel">Excel</button>
                        <button class="btn btn-sm btn-primary" data-table-export="#colors-table" data-table-export-type="word">Word</button>
                    </div>
                    <div class="table-responsive">
                        <table id="colors-table" data-server-pagination class="table table-bordered text-nowrap key-buttons">
                            <thead><tr><th>ID</th><th>Name</th><th>Color Code</th><th>Status</th><th>Action</th></tr></thead>
                            <tbody>
                                @foreach ($getColors as $color)
                                    <tr>
                                        <td>{{ $color['id'] }}</td>
                                        <td>{{ $color['name'] }}</td>
                                        <td><span class="d-inline-block border rounded me-2 align-middle" style="width: 24px; height: 24px; background-color: {{ $color['color_code'] }}"></span>{{ $color['color_code'] }}</td>
                                        <td>
                                            @if ($canEditColors)
                                                <button type="button" class="btn btn-sm {{ $color['status'] ? 'btn-success' : 'btn-secondary' }}"
                                                    data-crud-status data-url="{{ route('admin-color.status', $color['id']) }}">{{ $color['status'] ? 'Active' : 'Inactive' }}</button>
                                            @else
                                                {{ $color['status'] ? 'Active' : 'Inactive' }}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($canEditColors)
                                                <button type="button" class="btn btn-sm btn-primary" data-crud-edit data-crud-modal="#color-form-modal"
                                                    data-url="{{ route('admin-color.show', $color['id']) }}" data-update-url="{{ route('admin-color.update', $color['id']) }}">Edit</button>
                                            @endif
                                            @if ($canDeleteColors)
                                                <button type="button" class="btn btn-sm btn-danger" data-crud-delete data-url="{{ route('admin-color.delete', $color['id']) }}">Delete</button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{{ $getColors->links() }}</div>
                </div>
            </div></div></div>
        </div>
    </div>
</div>

<div class="modal fade" id="color-form-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content">
        <form data-crud-form>
            @csrf
            <div class="modal-header"><h5 class="modal-title" data-crud-title>Add Color</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="alert alert-danger d-none js-crud-errors"></div>
                <div class="mb-3"><label class="form-label">Color Name</label><input type="text" name="name" class="form-control" required maxlength="255"></div>
                <div class="mb-3"><label class="form-label">Color Code</label><input type="color" name="color_code" class="form-control form-control-color" value="#000000" required></div>
                <div><label class="form-label">Status</label><select name="status" class="form-select"><option value="1">Active</option><option value="0">Inactive</option></select></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary" data-crud-submit>Save Color</button></div>
        </form>
    </div></div>
</div>
