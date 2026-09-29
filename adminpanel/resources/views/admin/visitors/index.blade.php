@extends('admin.layout.layout')

@section('content')
<div class="app-content main-content"><div class="side-app"><div class="container-fluid main-container">
    <div class="page-header">
        <div class="page-leftheader">
            <h4 class="page-title">Visitors</h4>
            <p class="text-muted mb-0">Storefront visits and signed-in customer activity.</p>
        </div>
        <div class="page-rightheader d-flex flex-wrap gap-2">
            <div class="btn-group">
                @foreach (['today' => 'Today', 'week' => '7 days', 'month' => 'This month'] as $key => $label)
                    <a data-ajax-page href="{{ route('visitors.index', ['period' => $key]) }}" class="btn btn-sm {{ $period === $key ? 'btn-primary' : 'btn-outline-primary' }}">{{ $label }}</a>
                @endforeach
            </div>
            @include('admin.visitors.nav')
            @if(Auth::guard('admin')->user()?->hasModuleAccess('visitor', 'delete'))
                <form method="POST" action="{{ route('visitors.destroy') }}" onsubmit="return confirmVisitorDelete(event, this)">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete all data</button>
                </form>
            @endif
        </div>
    </div>

    <div class="row row-sm">
        <div class="col-12 col-sm-4"><div class="card visitor-stat"><div class="card-body"><p class="text-muted mb-2">Page visits</p><h3 class="mb-0">{{ number_format($totalVisits) }}</h3></div></div></div>
        <div class="col-12 col-sm-4"><div class="card visitor-stat"><div class="card-body"><p class="text-muted mb-2">Unique visitors</p><h3 class="mb-0">{{ number_format($uniqueVisitors) }}</h3></div></div></div>
        <div class="col-12 col-sm-4"><div class="card visitor-stat"><div class="card-body"><p class="text-muted mb-2">Signed-in customers</p><h3 class="mb-0">{{ number_format($loggedInVisitors) }}</h3></div></div></div>
    </div>

    <div class="row row-sm">
        <div class="col-xl-8"><div class="card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h3 class="card-title mb-0">Recent visits</h3>
                <form method="GET" action="{{ route('visitors.index') }}" data-ajax-filter class="d-flex gap-2">
                    <input type="hidden" name="period" value="{{ $period }}">
                    <input class="form-control form-control-sm visitor-search" name="search" value="{{ $search }}" placeholder="Search country, name, IP...">
                    <button class="btn btn-sm btn-primary">Search</button>
                </form>
            </div>
            <div class="table-responsive"><table class="table table-hover align-middle mb-0">
                <thead><tr><th class="ps-4">Time</th><th>Visitor</th><th>IP address</th><th>Page</th><th>Country / city</th><th class="pe-4">Device</th></tr></thead>
                <tbody>@forelse($logs as $log)<tr>
                    <td class="ps-4 text-nowrap">{{ $log->created_at->copy()->timezone('Asia/Dhaka')->format('d M, h:i A') }}</td>
                    <td>@if($log->user)<b>{{ $log->user->name }}</b><small class="d-block text-muted">{{ $log->user->email }}</small>@else <span class="text-muted">Guest visitor</span>@endif</td>
                    <td class="text-nowrap">{{ $log->ip_address ?: 'Not available' }}</td>
                    <td class="text-break">{{ $log->path }}</td>
                    <td>{{ collect([$log->country, $log->city])->filter()->implode(', ') ?: 'Not available' }}</td>
                    <td class="pe-4">{{ $log->device ?: 'Unknown' }}</td>
                </tr>@empty<tr><td colspan="6" class="text-center text-muted py-5">No visits recorded for this period yet.</td></tr>@endforelse</tbody>
            </table></div>
            <div class="card-footer d-flex flex-wrap align-items-center justify-content-between gap-2">
                <form method="GET" action="{{ route('visitors.index') }}" data-ajax-filter class="d-flex align-items-center gap-2">
                    <input type="hidden" name="period" value="{{ $period }}"><input type="hidden" name="search" value="{{ $search }}">
                    <span class="text-muted text-nowrap">Show</span>
                    <select name="per_page" class="form-select form-select-sm w-auto" data-ajax-filter-auto>@foreach([15,30,50] as $size)<option value="{{ $size }}" @selected($perPage === $size)>{{ $size }}</option>@endforeach</select>
                </form>
                @if($logs->hasPages()){{ $logs->links() }}@endif
            </div>
        </div></div>
        <div class="col-xl-4"><div class="card">
            <div class="card-header"><h3 class="card-title">Most visited pages</h3></div>
            <div class="card-body">@forelse($topPages as $page)<div class="visitor-page-row"><span class="text-break me-3">{{ $page->path }}</span><b>{{ $page->visits }}</b></div>@empty<p class="text-muted mb-0">No visit data yet.</p>@endforelse</div>
        </div></div>
    </div>
</div></div></div>
<style>
.visitor-stat{min-height:112px}.visitor-search{min-width:220px}.visitor-page-row{display:flex;justify-content:space-between;align-items:center;padding:11px 0;border-bottom:1px solid rgba(130,140,160,.18)}.visitor-page-row:last-child{border-bottom:0}@media(max-width:575px){.visitor-search{min-width:0}}
</style>
<script>
function confirmVisitorDelete(event, form) {
    event.preventDefault();

    Swal.fire({
        title: 'Delete all visitor data?',
        text: 'All visit history and live sessions will be permanently deleted.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete all',
        cancelButtonText: 'Keep data',
    }).then(function (result) {
        if (!result.isConfirmed) return;

        var $form = $(form);
        $form.find('button[type="submit"]').prop('disabled', true);

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: $form.serialize(),
            headers: { Accept: 'application/json' },
        }).done(function (response) {
            window.loadAjaxPage(response.redirect_url || window.location.href, true);
            setTimeout(function () { crudToast('success', response.message); }, 200);
        }).fail(function (xhr) {
            crudToast('error', xhr.responseJSON?.message || 'Could not delete visitor data.');
            $form.find('button[type="submit"]').prop('disabled', false);
        });
    });

    return false;
}
</script>
@endsection
