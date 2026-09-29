@extends('admin.layout.layout')
@section('content')
    <div class="app-content main-content">
        <div class="side-app">
            <div class="container-fluid main-container">
                <div class="page-header">
                    <div class="page-leftheader">
                        <h4 class="page-title">Traffic sources</h4>
                        <p class="text-muted mb-0">Where visitors came from before opening your store.</p>
                    </div>
                    <div class="page-rightheader">@include('admin.visitors.nav')</div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Traffic source summary</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Source</th>
                                    <th class="text-center">Unique visitors</th>
                                    <th class="text-end pe-4">Page visits</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sources as $source)
                                    <tr>
                                        <td class="ps-4"><b>{{ $source['source'] }}</b></td>
                                        <td class="text-center">{{ number_format($source['visitors']) }}</td>
                                        <td class="text-end pe-4">{{ number_format($source['visits']) }}</td>
                                    </tr>
                                @empty<tr>
                                        <td colspan="3" class="text-center text-muted py-5">No traffic source data yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
