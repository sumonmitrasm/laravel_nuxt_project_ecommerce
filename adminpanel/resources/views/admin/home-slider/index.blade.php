@extends('admin.layout.layout')
@section('content')
<style>
#home-slider-modal .modal-dialog{max-width:min(1120px,calc(100vw - 48px))!important;width:100%;margin:1.75rem auto}
#home-slider-modal .modal-content{border:0;border-radius:14px;overflow:hidden;box-shadow:0 24px 70px rgba(0,0,0,.32)}
#home-slider-modal .modal-header{padding:18px 24px;background:linear-gradient(135deg,#26366f,#4056bd);border:0;color:#fff}
#home-slider-modal .slider-modal-heading{display:flex;align-items:center;gap:13px}
#home-slider-modal .slider-modal-icon{display:grid;place-items:center;width:42px;height:42px;border-radius:11px;background:rgba(255,255,255,.14);font-size:20px}
#home-slider-modal .modal-title{margin:0;font-size:17px;font-weight:700}
#home-slider-modal .modal-subtitle{display:block;margin-top:2px;color:rgba(255,255,255,.72);font-size:11px}
#home-slider-modal .btn-close{filter:invert(1);opacity:.8}
#home-slider-modal .modal-body{padding:24px 26px;background:rgba(255,255,255,.015)}
#home-slider-modal .form-label{margin-bottom:7px;font-size:12px;font-weight:650}
#home-slider-modal .form-control,#home-slider-modal .form-select{min-height:42px;border-radius:7px}
#home-slider-modal textarea.form-control{min-height:96px;line-height:1.55}
#home-slider-modal input[type=file]{padding:5px}
#home-slider-modal [data-image-preview-for]{display:block!important;width:100%!important;height:112px!important;margin-top:10px!important;padding:7px;background:rgba(255,255,255,.04);object-fit:contain!important}
#home-slider-modal [data-image-preview-for].d-none{display:none!important}
#home-slider-modal .modal-footer{padding:14px 24px;border-top:1px solid rgba(128,145,190,.18);gap:8px}
#home-slider-modal .modal-footer .btn{min-width:110px;border-radius:7px}
@media(max-width:767px){#home-slider-modal .modal-dialog{max-width:calc(100vw - 20px)!important;margin:10px auto}#home-slider-modal .modal-body{padding:18px}}
</style>
@php
  $admin = Auth::guard('admin')->user();
  $canAdd = $admin?->hasModuleAccess('home_slider', 'add');
  $canEdit = $admin?->hasModuleAccess('home_slider', 'edit');
  $canDelete = $admin?->hasModuleAccess('home_slider', 'delete');
@endphp
<div class="app-content main-content"><div class="side-app"><div class="container-fluid main-container">
  <div class="page-header"><div class="page-leftheader"><h4 class="page-title">{{ $title }}</h4></div></div>
  <div class="card">
    <div class="card-header justify-content-between"><h3 class="card-title">Home Sliders</h3>
      @if($canAdd)<button type="button" class="btn btn-info" data-crud-create data-crud-modal="#home-slider-modal" data-store-url="{{ route('admin-home-slider.store') }}" data-create-title="Add Home Slider">Add Slider</button>@endif
    </div>
    <div class="card-body"><div class="table-responsive"><table class="table table-bordered align-middle text-nowrap">
      <thead><tr><th>ID</th><th>Preview</th><th>Title</th><th>Offer</th><th>Position</th><th>Status</th><th>Action</th></tr></thead><tbody>
      @forelse($sliders as $slider)<tr>
        <td>{{ $slider->id }}</td>
        <td>@if($slider->image_url)<img src="{{ $slider->image_url }}" alt="{{ $slider->title }}" class="rounded border" style="width:90px;height:58px;object-fit:contain">@else <span class="text-muted">No image</span> @endif</td>
        <td><strong>{{ $slider->title }}</strong>@if($slider->eyebrow)<small class="d-block text-muted">{{ $slider->eyebrow }}</small>@endif</td>
        <td>{{ $slider->offer_text ?: '—' }}</td><td>{{ $slider->position }}</td>
        <td>@if($canEdit)<button type="button" class="btn btn-sm {{ $slider->status ? 'btn-success' : 'btn-secondary' }}" data-crud-status data-url="{{ route('admin-home-slider.status', $slider) }}">{{ $slider->status ? 'Active' : 'Inactive' }}</button>@else {{ $slider->status ? 'Active' : 'Inactive' }} @endif</td>
        <td>@if($canEdit)<button type="button" class="btn btn-sm btn-primary" data-crud-edit data-crud-modal="#home-slider-modal" data-url="{{ route('admin-home-slider.show', $slider) }}" data-update-url="{{ route('admin-home-slider.update', $slider) }}">Edit</button>@endif @if($canDelete)<button type="button" class="btn btn-sm btn-danger" data-crud-delete data-url="{{ route('admin-home-slider.delete', $slider) }}">Delete</button>@endif</td>
      </tr>@empty<tr><td colspan="7" class="text-center text-muted py-5">No home slider has been added yet.</td></tr>@endforelse
      </tbody></table></div></div>
  </div>
</div></div></div>
<div class="modal fade" id="home-slider-modal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content">
  <form data-crud-form enctype="multipart/form-data">@csrf
    <div class="modal-header"><h5 class="modal-title" data-crud-title>Add Home Slider</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="alert alert-danger d-none js-crud-errors"></div>@include('admin.home-slider.form')</div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary" data-crud-submit>Save Slider</button></div>
  </form>
</div></div></div>
@endsection
