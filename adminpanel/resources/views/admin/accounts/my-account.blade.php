@extends('admin.layout.layout')
@section('content')
    <div class="app-content main-content">
        <div class="side-app">
            <div class="container-fluid main-container">
                <div class="page-header">
                    <div class="page-leftheader">
                        <h4 class="page-title">My Account</h4>
                        <p class="text-muted mb-0">Update your own admin account details.</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-7">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Account details</h3>
                            </div>
                            <div class="card-body">
                                <form data-purchase-form method="POST" action="{{ route('admin-my-account.update') }}">
                                    @csrf @method('PUT')
                                    <div class="mb-3"><label class="form-label">Name *</label><input class="form-control"
                                            name="name" value="{{ old('name', $admin->name) }}" required></div>
                                    <div class="mb-3"><label class="form-label">Email</label><input class="form-control"
                                            value="{{ $admin->email }}" readonly><small class="text-muted">Email cannot be
                                            changed from My Account.</small></div>
                                    <div class="mb-3"><label class="form-label">Mobile</label><input class="form-control"
                                            name="mobile" value="{{ old('mobile', $admin->mobile) }}" maxlength="30"></div>
                                    <hr>
                                    <p class="text-muted">Leave password fields empty if you do not want to change your
                                        password.</p>
                                    <div class="mb-3"><label class="form-label">New password</label><input
                                            class="form-control" type="password" name="password" minlength="6"></div>
                                    <div class="mb-3"><label class="form-label">Confirm new password</label><input
                                            class="form-control" type="password" name="password_confirmation"
                                            minlength="6"></div>
                                    <button class="btn btn-primary">Save changes</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-5">
                        <div class="card">
                            <div class="card-body text-center"><img class="avatar avatar-xxl brround mb-3"
                                    src="{{ $admin->image ? asset('admin/adminimage/' . $admin->image) : asset('admin/site_settings/no-image.png') }}"
                                    alt="Profile image">
                                <h4 class="mb-1">{{ $admin->name }}</h4>
                                <p class="text-muted mb-0">{{ ucfirst($admin->type) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
