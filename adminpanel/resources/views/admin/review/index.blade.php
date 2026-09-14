@extends('admin.layout.layout')
@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="page-title">Product Reviews</h1>
            </div>
            @if (session('success_message'))
                <div class="alert alert-success">{{ session('success_message') }}</div>
            @endif
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Customer Reviews</h3>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Rating</th>
                                <th>Review</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reviews as $review)
                                <tr>
                                    <td>{{ $review->user?->name }} @if ($review->verified_purchase)
                                            <span class="badge bg-success">Verified</span>
                                        @endif
                                    </td>
                                    <td>{{ $review->product?->product_name }}</td>
                                    <td style="color:#ffad21">{{ str_repeat('★', $review->rating) }}</td>
                                    <td style="min-width:260px"><strong>{{ $review->title }}</strong>
                                        <div>{{ $review->comment }}</div>
                                    </td>
                                    <td><span
                                            class="badge {{ $review->status === 'approved' ? 'bg-success' : ($review->status === 'rejected' ? 'bg-danger' : 'bg-warning') }}">{{ ucfirst($review->status) }}</span>
                                    </td>
                                    <td class="text-nowrap">
                                        <button type="button" class="btn btn-sm btn-success" data-crud-status data-url="{{ route('admin-reviews.status', [$review, 'approved']) }}">Approve</button>
                                        <button type="button" class="btn btn-sm btn-warning" data-crud-status data-url="{{ route('admin-reviews.status', [$review, 'rejected']) }}">Reject</button>
                                        <button type="button" class="btn btn-sm btn-danger" data-crud-delete data-url="{{ route('admin-reviews.delete', $review) }}">Delete</button>
                                    </td>
                            </tr>@empty<tr>
                                    <td colspan="6" class="text-center">No reviews found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>{{ $reviews->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
