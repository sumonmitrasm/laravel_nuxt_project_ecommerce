<div class="row g-3">
  <div class="col-md-3"><label>Eyebrow</label><input class="form-control" name="eyebrow" value="{{ old('eyebrow',$slider?->eyebrow) }}"></div>
  <div class="col-md-5"><label>Title *</label><input class="form-control" required name="title" value="{{ old('title',$slider?->title) }}"></div>
  <div class="col-md-4"><label>Image (max 2MB)</label><input class="form-control" type="file" name="image" accept="image/*"></div>
  <div class="col-12"><label>Description</label><textarea class="form-control" name="description">{{ old('description',$slider?->description) }}</textarea></div>
  <div class="col-md-2"><label>Offer label</label><input class="form-control" name="offer_label" value="{{ old('offer_label',$slider?->offer_label) }}"></div>
  <div class="col-md-2"><label>Offer text</label><input class="form-control" name="offer_text" value="{{ old('offer_text',$slider?->offer_text) }}"></div>
  <div class="col-md-3"><label>Offer note</label><input class="form-control" name="offer_note" value="{{ old('offer_note',$slider?->offer_note) }}"></div>
  <div class="col-md-2"><label>Button text</label><input class="form-control" name="button_text" value="{{ old('button_text',$slider?->button_text) }}"></div>
  <div class="col-md-3"><label>Button URL</label><input class="form-control" name="button_url" value="{{ old('button_url',$slider?->button_url) }}"></div>
  <div class="col-md-2"><label>Background</label><input class="form-control form-control-color" type="color" name="background_color" value="{{ old('background_color',$slider?->background_color ?? '#f4f6ed') }}"></div>
  <div class="col-md-2"><label>Position</label><input class="form-control" type="number" min="0" name="position" value="{{ old('position',$slider?->position ?? 0) }}"></div>
  <div class="col-md-2"><label>Status</label><select class="form-select" name="status"><option value="1" @selected(old('status',$slider?->status ?? true))>Active</option><option value="0" @selected(!old('status',$slider?->status ?? true))>Inactive</option></select></div>
  @if($slider?->image_url)<div class="col-md-3"><img src="{{ $slider->image_url }}" alt="" style="max-width:180px;max-height:100px;object-fit:contain"></div>@endif
</div>
