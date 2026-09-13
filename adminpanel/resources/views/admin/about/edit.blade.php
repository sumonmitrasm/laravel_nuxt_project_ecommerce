@extends('admin.layout.layout')

@section('content')
<div class="app-content main-content">
    <div class="side-app">
        <div class="container-fluid main-container">
            <div class="page-header"><div class="page-leftheader"><h4 class="page-title">About Page</h4></div></div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form id="about-page-form" method="POST" action="{{ route('admin-about.update') }}">
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Edit About Page</h3></div>
                    <div class="card-body">
                        <div id="about-form-errors" class="alert alert-danger d-none"></div>
                        @if ($errors->any())
                            <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                        @endif

                        <h6 class="fw-bold text-primary mb-3">Hero Section</h6>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Hero Title</label><input class="form-control" name="hero_title" value="{{ old('hero_title', $about->hero_title) }}" placeholder="Better products."></div>
                            <div class="col-md-6"><label class="form-label">Highlighted Title</label><input class="form-control" name="hero_highlight" value="{{ old('hero_highlight', $about->hero_highlight) }}" placeholder="Better everyday living."></div>
                            <div class="col-12"><label class="form-label">Hero Description</label><textarea class="form-control" name="hero_text" rows="3">{{ old('hero_text', $about->hero_text) }}</textarea></div>
                        </div>

                        <hr class="my-4"><h6 class="fw-bold text-primary mb-3">Who We Are</h6>
                        <div class="row g-3">
                            <div class="col-md-8"><label class="form-label">Title</label><input class="form-control" name="intro_title" value="{{ old('intro_title', $about->intro_title) }}"></div>
                            <div class="col-md-4"><label class="form-label">Return Days</label><input type="number" class="form-control" name="return_days" min="1" max="365" value="{{ old('return_days', $about->return_days ?: 7) }}" required></div>
                            <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="intro_text" rows="4">{{ old('intro_text', $about->intro_text) }}</textarea></div>
                        </div>

                        <hr class="my-4"><h6 class="fw-bold text-primary mb-3">Our Values</h6>
                        <div class="row g-3">
                            @for ($number = 1; $number <= 4; $number++)
                                <div class="col-md-6"><label class="form-label">Value {{ $number }} Title</label><input class="form-control" name="value_{{ $number }}_title" value="{{ old('value_'.$number.'_title', $about->{'value_'.$number.'_title'}) }}"></div>
                                <div class="col-md-6"><label class="form-label">Value {{ $number }} Description</label><textarea class="form-control" name="value_{{ $number }}_text" rows="2">{{ old('value_'.$number.'_text', $about->{'value_'.$number.'_text'}) }}</textarea></div>
                            @endfor
                        </div>

                        <hr class="my-4"><h6 class="fw-bold text-primary mb-3">Promise & CTA</h6>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Promise Title</label><input class="form-control" name="promise_title" value="{{ old('promise_title', $about->promise_title) }}"></div>
                            <div class="col-md-6"><label class="form-label">Bottom CTA Title</label><input class="form-control" name="cta_title" value="{{ old('cta_title', $about->cta_title) }}"></div>
                            <div class="col-12"><label class="form-label">Promise Description</label><textarea class="form-control" name="promise_text" rows="3">{{ old('promise_text', $about->promise_text) }}</textarea></div>
                        </div>

                        <hr class="my-4"><h6 class="fw-bold text-primary mb-3">SEO</h6>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Meta Title</label><input class="form-control" name="meta_title" value="{{ old('meta_title', $about->meta_title) }}"></div>
                            <div class="col-md-6"><label class="form-label">Meta Description</label><textarea class="form-control" name="meta_description" rows="2">{{ old('meta_description', $about->meta_description) }}</textarea></div>
                        </div>
                    </div>
                    <div class="card-footer text-end"><button id="about-save-button" class="btn btn-primary" type="submit">Save About Page</button></div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('about-page-form');
    const button = document.getElementById('about-save-button');
    const errorBox = document.getElementById('about-form-errors');

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        button.disabled = true;
        button.textContent = 'Saving...';
        errorBox.classList.add('d-none');

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: new FormData(form)
            });
            const data = await response.json();

            if (!response.ok) {
                const errors = data.errors ? Object.values(data.errors).flat().join('<br>') : (data.message || 'Update failed.');
                errorBox.innerHTML = errors;
                errorBox.classList.remove('d-none');
                return;
            }

            if (window.Swal) {
                Swal.fire({ position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 1500 });
            } else {
                alert(data.message);
            }
        } catch (error) {
            errorBox.textContent = 'Something went wrong. Please try again.';
            errorBox.classList.remove('d-none');
        } finally {
            button.disabled = false;
            button.textContent = 'Save About Page';
        }
    });
});
</script>
@endsection