@extends('admin.layout.layout')
@section('content')
    <div class="app-content main-content">
        <div class="side-app">
            <div class="container-fluid main-container">
                <div class="page-header">
                    <div class="page-leftheader">
                        <h4 class="page-title">Live visitors</h4>
                        <p class="text-muted mb-0">Browsers active during the last 5 minutes.</p>
                    </div>
                    <div class="page-rightheader">@include('admin.visitors.nav')</div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Active now: {{ $visitors->total() }}</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Last activity</th>
                                    <th>Visitor</th>
                                    <th>IP address</th>
                                    <th>Current page</th>
                                    <th>Country / city</th>
                                    <th class="pe-4">Device</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($visitors as $visitor)
                                    <tr>
                                        <td class="ps-4 text-nowrap">{{ $visitor->last_seen_at->copy()->timezone('Asia/Dhaka')->format('h:i A') }}</td>
                                        <td>
                                            @if ($visitor->user)
                                                <b>{{ $visitor->user->name }}</b><small
                                                class="d-block text-muted">{{ $visitor->user->email }}</small>@else<span
                                                    class="text-muted">Guest visitor</span>
                                            @endif
                                        </td>
                                        <td>{{ $visitor->ip_address ?: 'Not available' }}</td>
                                        <td class="text-break">{{ $visitor->path }}</td>
                                        <td>{{ collect([$visitor->country, $visitor->city])->filter()->implode(', ') ?:'Not available' }}
                                        </td>
                                        <td class="pe-4">{{ $visitor->device }}</td>
                                    </tr>
                                @empty<tr>
                                        <td colspan="6" class="text-center text-muted py-5">No active visitors in the
                                            last 5 minutes.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($visitors->hasPages())
                        <div class="card-footer">{{ $visitors->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
