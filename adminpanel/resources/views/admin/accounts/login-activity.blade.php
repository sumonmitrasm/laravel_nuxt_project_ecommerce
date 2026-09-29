@extends('admin.layout.layout')
@section('content')
    <div class="app-content main-content">
        <div class="side-app">
            <div class="container-fluid main-container">
                <div class="page-header">
                    <div class="page-leftheader">
                        <h4 class="page-title">Login activity</h4>
                        <p class="text-muted mb-0">Successful admin and staff sign-ins.</p>
                    </div>
                    <div class="page-rightheader d-flex gap-2">
                        <form method="GET" action="{{ route('admin-login-activity.index') }}" data-ajax-filter
                            class="d-flex gap-2"><input class="form-control form-control-sm" name="search"
                                value="{{ $search }}" placeholder="Search name, email, IP..."><button
                                class="btn btn-sm btn-primary">Search</button></form>
                        @if (Auth::guard('admin')->user()?->hasModuleAccess('login_activity', 'delete'))
                            <form method="POST" action="{{ route('admin-login-activity.destroy') }}"
                                onsubmit="return confirmLoginActivityDelete(event, this)">@csrf @method('DELETE')<button
                                    class="btn btn-sm btn-outline-danger">Delete all</button></form>
                        @endif
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Login history</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Time</th>
                                    <th>Admin</th>
                                    <th>IP address</th>
                                    <th class="pe-4">Device</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activities as $activity)
                                    <tr>
                                        <td class="ps-4">{{ $activity->logged_in_at->format('d M Y, h:i A') }}</td>
                                        <td><b>{{ $activity->admin?->name ?? 'Deleted admin' }}</b><small
                                                class="d-block text-muted">{{ $activity->admin?->email }}</small></td>
                                        <td>{{ $activity->ip_address ?: 'Not available' }}</td>
                                        <td class="pe-4">{{ $activity->device ?: 'Unknown' }}</td>
                                </tr>@empty<tr>
                                        <td colspan="4" class="text-center text-muted py-5">No login activity yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($activities->hasPages())
                        <div class="card-footer">{{ $activities->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <script>
        function confirmLoginActivityDelete(event, form) {
            event.preventDefault();
            Swal.fire({
                title: 'Delete login activity?',
                text: 'This cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                confirmButtonText: 'Yes, delete all',
                cancelButtonText: 'Keep data'
            }).then(function(result) {
                if (!result.isConfirmed) return;
                var $form = $(form);
                $form.find('button').prop('disabled', true);
                $.ajax({
                    url: $form.attr('action'),
                    method: 'POST',
                    data: $form.serialize(),
                    headers: {
                        Accept: 'application/json'
                    }
                }).done(function(response) {
                    window.loadAjaxPage(response.redirect_url || window.location.href, true);
                    setTimeout(function() {
                        crudToast('success', response.message)
                    }, 200)
                }).fail(function(xhr) {
                    crudToast('error', xhr.responseJSON?.message || 'Could not delete login activity.');
                    $form.find('button').prop('disabled', false)
                })
            });
            return false;
        }
    </script>
@endsection
