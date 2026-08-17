@extends('admin.layout.layout')

@section('content')
    @php
        $currentAdmin = Auth::guard('admin')->user();
        $canAddAttributes = $currentAdmin?->hasModuleAccess('attribute', 'add');
        $canEditAttributes = $currentAdmin?->hasModuleAccess('attribute', 'edit');
        $canDeleteAttributes = $currentAdmin?->hasModuleAccess('attribute', 'delete');
        $canViewProducts = $currentAdmin?->hasModuleAccess('product', 'view');
    @endphp
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between my-4">
                <div>
                    <h2 class="mb-1">Product Attributes</h2>
                    <p class="text-muted mb-0">Manage reusable options such as Size, Color, RAM and Storage.</p>
                </div>
                @if ($canViewProducts)<a href="{{ route('products') }}" class="btn btn-light">Back to Products</a>@endif
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            @if ($canAddAttributes)
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="mb-3">Add Attribute</h5>
                    <form method="POST" action="{{ route('product-attributes.store') }}" class="row g-3 align-items-end"
                        data-product-attribute-form>
                        @csrf
                        <div class="col-md-5"><label class="form-label">Name</label><input name="name"
                                class="form-control" placeholder="e.g. RAM" required></div>
                        <div class="col-md-4"><label class="form-label">Type</label><select name="type"
                                class="form-select">
                                <option value="text">Text</option>
                                <option value="color">Color</option>
                            </select></div>
                        <div class="col-md-3"><button class="btn btn-primary w-100">Add Attribute</button></div>
                    </form>
                </div>
            </div>
            @endif

            <div class="row">
                @forelse ($attributes as $attribute)
                    <div class="col-xl-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">{{ $attribute->name }}</h5><small
                                        class="text-muted">{{ ucfirst($attribute->type) }}</small>
                                </div>
                                @if ($canDeleteAttributes)<form method="POST" action="{{ route('product-attributes.destroy', $attribute) }}"
                                    data-product-attribute-form>@csrf @method('DELETE')<button
                                        class="btn btn-sm btn-outline-danger"><i class="fe fe-trash-2"></i></button></form>@endif
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    @forelse ($attribute->values as $value)
                                        <span
                                            class="badge bg-light text-dark border d-inline-flex align-items-center gap-2 py-2">
                                            @if ($value->color_code)
                                                <span class="rounded-circle border"
                                                    style="width:16px;height:16px;background:{{ $value->color_code }}"></span>
                                            @endif
                                            {{ $value->value }}
                                            @if ($canDeleteAttributes)<form method="POST"
                                                action="{{ route('product-attribute-values.destroy', $value) }}"
                                                class="d-inline" data-product-attribute-form>@csrf @method('DELETE')<button
                                                    class="btn p-0 text-danger" aria-label="Delete value">&times;</button>
                                            </form>@endif
                                        </span>
                                    @empty <span class="text-muted">No values yet.</span>
                                    @endforelse
                                </div>
                                @if ($canEditAttributes)<form method="POST" action="{{ route('product-attributes.values.store', $attribute) }}"
                                    class="row g-2" data-product-attribute-form>
                                    @csrf
                                    <div class="col"><input name="value" class="form-control" placeholder="New value"
                                            required></div>
                                    @if ($attribute->type === 'color')
                                        <div class="col-auto"><input type="color" name="color_code"
                                                class="form-control form-control-color" value="#000000"></div>
                                    @endif
                                    <div class="col-auto"><button class="btn btn-outline-primary">Add Value</button></div>
                                </form>@endif
                            </div>
                        </div>
                    </div>
                @empty <div class="col-12">
                        <div class="alert alert-info">Create your first attribute.</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
