@extends('admin.layout.layout')
@section('content')
    <div class="app-content main-content">
        <div class="side-app">
            <div class="container-fluid main-container">
                <div class="page-header">
                    <div class="page-leftheader">
                        <h4 class="page-title">Countries &amp; cities</h4>
                        <p class="text-muted mb-0">Visitor locations from IP-based GeoIP lookup.</p>
                    </div>
                    <div class="page-rightheader">@include('admin.visitors.nav')</div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Location summary</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Country</th>
                                    <th>City</th>
                                    <th class="text-center">Unique visitors</th>
                                    <th class="text-end pe-4">Page visits</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($locations as $location)
                                    <tr>
                                        <td class="ps-4"><b>{{ $location->country }}</b></td>
                                        <td>{{ $location->city ?: 'Not available' }}</td>
                                        <td class="text-center">{{ number_format($location->visitors) }}</td>
                                        <td class="text-end pe-4">{{ number_format($location->visits) }}</td>
                                    </tr>
                                @empty<tr>
                                        <td colspan="4" class="text-center text-muted py-5">No location data yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($locations->hasPages())
                        <div class="card-footer">{{ $locations->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
