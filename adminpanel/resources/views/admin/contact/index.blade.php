@extends('admin.layout.layout')
@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="page-title">Contact Messages</h1>
            </div>
            <div class="card">
                <div class="card-body table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Topic</th>
                                <th>Message</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($messages as $message)
                                <tr>
                                    <td>{{ $message->first_name }} {{ $message->last_name }}</td>
                                    <td>{{ $message->email }}<br>{{ $message->phone }}</td>
                                    <td>{{ $message->topic }}</td>
                                    <td style="min-width:300px">{{ $message->message }}</td>
                                    <td>{{ $message->created_at->format('d M Y, h:i A') }}</td>
                                    <td><button type="button" class="btn btn-sm btn-danger" data-crud-delete
                                            data-url="{{ route('admin-contact.delete', $message) }}">Delete</button></td>
                            </tr>@empty<tr>
                                    <td colspan="6" class="text-center">No messages found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{ $messages->links() }}
                </div>
            </div>
            <form class="d-none" data-crud-form>@csrf</form>
        </div>
    </div>
@endsection
