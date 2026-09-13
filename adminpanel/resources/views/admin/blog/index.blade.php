@extends('admin.layout.layout')

@section('content')
@php
    $admin = Auth::guard('admin')->user();
    $canAddBlog = $admin?->hasModuleAccess('blog', 'add');
    $canEditBlog = $admin?->hasModuleAccess('blog', 'edit');
    $canDeleteBlog = $admin?->hasModuleAccess('blog', 'delete');
@endphp
<style>
    .blog-admin-table { width: 100%; min-width: 1050px; table-layout: fixed; }
    .blog-admin-table .blog-column { width: 50%; }
    .blog-admin-table .author-column { width: 13%; }
    .blog-admin-table .date-column { width: 18%; }
    .blog-admin-table .status-column { width: 9%; }
    .blog-admin-table .action-column { width: 10%; }
    .blog-admin-table .blog-cell-wrap,
    .blog-admin-table .blog-copy { min-width: 0; }
    .blog-admin-table .blog-title { max-width: 100%; display: -webkit-box; overflow: hidden; white-space: normal; overflow-wrap: anywhere; -webkit-box-orient: vertical; -webkit-line-clamp: 2; line-height: 1.4; }
    .blog-admin-table .blog-slug { max-width: 100%; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .blog-admin-table .blog-tags { white-space: normal; }
</style>
<div class="app-content main-content">
    <div class="side-app"><div class="container-fluid main-container">
        <div class="page-header"><div class="page-leftheader"><h4 class="page-title">{{ $title }}</h4></div></div>
        <div class="card">
            <div class="card-header justify-content-between"><div class="card-title">All Blogs</div>
                @if ($canAddBlog)<button type="button" class="btn btn-info" data-crud-create data-crud-modal="#blog-form-modal" data-store-url="{{ route('admin-blog.store') }}" data-create-title="Add Blog">Add Blog</button>@endif
            </div>
            <div class="card-body">
                <div class="mb-3 d-flex align-items-center gap-2"><label class="mb-0">Show</label><select class="form-select form-select-sm w-auto" data-server-per-page>@foreach ([10,20,50,100] as $size)<option value="{{ $size }}" {{ (int) request('per_page',10) === $size ? 'selected' : '' }}>{{ $size }}</option>@endforeach</select><span>entries</span></div>
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap blog-admin-table" data-server-pagination>
                        <thead><tr><th class="blog-column">Blog</th><th class="author-column">Author</th><th class="date-column">Publish date</th><th class="status-column">Status</th><th class="text-center action-column">Action</th></tr></thead>
                        <tbody>
                        @forelse ($blogs as $blog)
                            <tr>
                                <td><div class="d-flex align-items-center gap-2 blog-cell-wrap">
                                    @if ($blog->image)<img src="{{ asset('admin/blogimage/'.$blog->image) }}" alt="{{ $blog->title }}" class="rounded border" style="width:46px;height:38px;object-fit:cover">@else<span class="avatar avatar-sm bg-primary-transparent"><i class="fe fe-file-text"></i></span>@endif
                                    <div class="blog-copy"><div class="fw-semibold blog-title" title="{{ $blog->title }}">{{ $blog->title }}</div><small class="text-muted blog-slug" title="{{ $blog->slug }}">{{ $blog->slug }}</small>@if ($blog->tags->isNotEmpty())<div class="mt-1 blog-tags">@foreach ($blog->tags as $tag)<span class="badge bg-primary-transparent text-primary me-1">{{ $tag->name }}</span>@endforeach</div>@endif</div>
                                </div></td>
                                <td>{{ $blog->author?->name ?: '—' }}</td>
                                <td>{{ $blog->published_at?->format('d M Y, h:i A') ?: 'Not scheduled' }}</td>
                                <td>@if ($canEditBlog)<button type="button" class="btn btn-sm {{ $blog->status ? 'btn-success' : 'btn-secondary' }}" data-crud-status data-url="{{ route('admin-blog.status', $blog) }}">{{ $blog->status ? 'Active' : 'Inactive' }}</button>@else<span class="badge {{ $blog->status ? 'bg-success' : 'bg-secondary' }}">{{ $blog->status ? 'Active' : 'Inactive' }}</span>@endif</td>
                                <td class="text-center">
                                    @if ($canEditBlog)<button type="button" class="btn btn-sm btn-primary" data-crud-edit data-crud-modal="#blog-form-modal" data-url="{{ route('admin-blog.show', $blog) }}" data-update-url="{{ route('admin-blog.update', $blog) }}">Edit</button>@endif
                                    @if ($canDeleteBlog)<button type="button" class="btn btn-sm btn-outline-danger" data-crud-delete data-url="{{ route('admin-blog.delete', $blog) }}">Delete</button>@endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-5">No blogs found.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $blogs->links() }}</div>
            </div>
        </div>
    </div></div>
</div>

<div class="modal fade" id="blog-form-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" style="max-width:920px;height:calc(100vh - 2rem);margin:1rem auto">
        <div class="modal-content h-100" style="overflow:hidden">
            <form data-crud-form enctype="multipart/form-data" class="d-flex flex-column h-100" style="min-height:0">@csrf
                <div class="modal-header flex-shrink-0">
                    <div><h5 class="modal-title mb-1" data-crud-title>Add Blog</h5><small class="text-muted">Create and manage your blog content</small></div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="overflow-y:auto;min-height:0">
                    <div class="alert alert-danger d-none js-crud-errors"></div>
                    <div class="row g-4">
                        <div class="col-lg-8">
                            <div class="card border mb-0"><div class="card-body">
                                <h6 class="fw-semibold mb-3">Blog content</h6>
                                <div class="mb-3"><label class="form-label">Title <span class="text-danger">*</span></label><input name="title" class="form-control" maxlength="255" placeholder="Enter blog title" required></div>
                                <div class="mb-3"><label class="form-label">Short Description</label><textarea name="excerpt" class="form-control" rows="3" maxlength="1000" placeholder="A short summary for the blog list"></textarea></div>
                                <div><label class="form-label">Full Content <span class="text-danger">*</span></label><textarea name="content" class="form-control" rows="9" placeholder="Write the complete blog content" required></textarea></div>
                            </div></div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card border mb-3"><div class="card-body">
                                <h6 class="fw-semibold mb-3">Publish settings</h6>
                                <div class="mb-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="1">Active</option><option value="0">Inactive</option></select></div>
                                <div class="mb-3"><label class="form-label">Publish Date</label><input type="datetime-local" name="published_at" class="form-control"><small class="text-muted">Leave empty to publish without scheduling.</small></div>
                                <div class="mb-3"><label class="form-label">Tags</label><select name="tag_ids[]" class="form-select" multiple size="5">@foreach ($tags as $tag)<option value="{{ $tag->id }}">{{ $tag->name }}</option>@endforeach</select><small class="text-muted">Hold Ctrl to select multiple tags.</small></div>
                                <div><label class="form-label">Featured Image</label><input type="file" name="image" class="form-control" accept="image/*" data-image-input><img data-image-preview-for="image" class="d-none mt-2 rounded border w-100" alt="Blog preview" style="height:130px;object-fit:cover"></div>
                            </div></div>
                        </div>
                        <div class="col-12">
                            <div class="card border mb-0"><div class="card-body">
                                <h6 class="fw-semibold mb-1">SEO information <small class="text-muted">(Optional)</small></h6>
                                <p class="text-muted mb-3">You may leave these fields empty. The blog title and short description can be used as fallbacks.</p>
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Google Search Title</label><input name="meta_title" class="form-control" maxlength="255" placeholder="Example: Best Laptop Buying Guide"><small class="text-muted">The main title shown in Google results. Recommended length: 50–60 characters.</small></div>
                                    <div class="col-md-6"><label class="form-label">Search Keywords</label><input name="meta_keywords" class="form-control" maxlength="255" placeholder="laptop, buying guide, technology"><small class="text-muted">Separate keywords with commas. This field is optional.</small></div>
                                    <div class="col-md-8"><label class="form-label">Google Search Description</label><textarea name="meta_description" class="form-control" rows="3" maxlength="500" placeholder="Short description shown in Google search results"></textarea><small class="text-muted">Recommended length: 150–160 characters.</small></div>
                                    <div class="col-md-4"><label class="form-label">Google Visibility</label><select name="meta_robot" class="form-select"><option value="index, follow">Show in Google</option><option value="noindex, follow">Hide from Google</option><option value="index, nofollow">Show, but do not follow links</option><option value="noindex, nofollow">Hide and do not follow links</option></select><small class="text-muted">Use “Show in Google” for a normal public blog.</small></div>
                                </div>
                            </div></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex-shrink-0"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary px-4" data-crud-submit>Save Blog</button></div>
            </form>
        </div>
    </div>
</div>
@endsection