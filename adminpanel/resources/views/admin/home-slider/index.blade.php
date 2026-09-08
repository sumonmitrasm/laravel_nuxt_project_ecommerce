@extends('admin.layout.layout')
@section('content')
<div class="main-content app-content mt-0"><div class="side-app"><div class="main-container container-fluid">
  <div class="page-header"><h1 class="page-title">Home Sliders</h1></div>
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
  <div class="card"><div class="card-header"><h3 class="card-title">Add slider</h3></div><div class="card-body">
    <form method="POST" action="{{ route('admin-home-slider.store') }}" enctype="multipart/form-data">@csrf
      @include('admin.home-slider.form', ['slider' => null])
      <button class="btn btn-primary mt-3">Add slider</button>
    </form>
  </div></div>
  @foreach($sliders as $slider)
  <div class="card"><div class="card-body"><form method="POST" action="{{ route('admin-home-slider.update',$slider) }}" enctype="multipart/form-data">@csrf @method('PUT')
    @include('admin.home-slider.form', ['slider' => $slider])
    <button class="btn btn-primary mt-3">Save changes</button>
  </form><div class="d-flex gap-2 mt-2">
    <form method="POST" action="{{ route('admin-home-slider.status',$slider) }}">@csrf @method('PATCH')<button class="btn btn-warning">{{ $slider->status ? 'Disable' : 'Enable' }}</button></form>
    <form method="POST" action="{{ route('admin-home-slider.delete',$slider) }}" onsubmit="return confirm('Delete this slider?')">@csrf @method('DELETE')<button class="btn btn-danger">Delete</button></form>
  </div></div></div>
  @endforeach
</div></div></div>
@endsection
