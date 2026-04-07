@extends('layout.default')

@push('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-lite.min.css" rel="stylesheet" crossorigin="anonymous">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"  crossorigin="anonymous">
<link href="/assets/css/page-editor.css" rel="stylesheet">
@endpush

@push('js')
<script src="https://code.jquery.com/jquery-3.7.0.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-lite.min.js"  crossorigin="anonymous"></script>
<script src="/assets/js/page-editor.js"></script>
@endpush

@section('content')
<div class="container-fluid py-4" id="page-editor-container">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-12">
          
            @include('partial.error-messages')

            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-gradient text-white text-center py-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="d-flex align-items-center justify-content-center">
                        <i class="fas fa-{{ isset($page) ? 'edit' : 'plus-circle' }} me-3 fs-4"></i>
                        <h1 class="h3 mb-0 fw-bold">{{ isset($page) ? 'Edit Page' : 'Create New Page' }}</h1>
                    </div>
                    @if(isset($page))
                    <small class="opacity-75">Last updated: {{ $page->updated_at->format('M d, Y g:i A') }}</small>
                    @endif
                </div>
                
                <div class="card-body p-0">
                    <form id="page-form" 
                          action="{{ isset($page) ? route('pages.update', $page->id) : route('pages.store') }}"
                          method="POST" 
                          enctype="multipart/form-data" 
                          class="needs-validation" 
                          novalidate
                          data-unsaved-changes="false">
                        
                        @csrf
                        @isset($page) @method('PUT') @endisset

                        <fieldset class="border-0">
                            <legend class="h5 mb-0 text-primary d-flex align-items-center">
                                <i class="fas fa-info-circle me-2"></i>
                                Basic Information
                            </legend>
                            
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="pageName" class="form-label fw-semibold d-flex align-items-center">
                                        <i class="fas fa-file-alt me-2 text-muted"></i>
                                        Page Name 
                                        <span class="text-danger ms-1" aria-label="required">*</span>
                                    </label>
                                    <input type="text" 
                                           name="name" 
                                           id="pageName" 
                                           value="{{ old('name', $page->name ?? '') }}"
                                           class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                           required 
                                           autocomplete="off"
                                           placeholder="Enter a descriptive page name"
                                           aria-describedby="pageNameHelp">
                                    <div id="pageNameHelp" class="form-text">
                                        <i class="fas fa-lightbulb me-1"></i>
                                        This will be displayed as the page title
                                    </div>
                                    @error('name')
                                        <div class="invalid-feedback">
                                            <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="paramlink" class="form-label fw-semibold d-flex align-items-center">
                                        <i class="fas fa-link me-2 text-muted"></i>
                                        URL Slug 
                                        <span class="text-danger ms-1" aria-label="required">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-globe text-muted"></i>
                                        </span>
                                        <input type="text" 
                                               name="paramlink" 
                                               id="paramlink" 
                                               value="{{ old('paramlink', $page->paramlink ?? '') }}"
                                               class="form-control form-control-lg @error('paramlink') is-invalid @enderror" 
                                               required
                                               pattern="^[a-z0-9]+(?:-[a-z0-9]+)*$"
                                               placeholder="auto-generated-url-slug"
                                               aria-describedby="paramlinkHelp">
                                    </div>
                                    <div id="paramlinkHelp" class="form-text">
                                        <i class="fas fa-magic me-1"></i>
                                        Auto-generated from page name (can be customized)
                                    </div>
                                    @error('paramlink')
                                        <div class="invalid-feedback">
                                            <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="border-0 mt-4">
                            <legend class="h5 mb-0 text-primary d-flex align-items-center">
                                <i class="fas fa-sitemap me-2"></i>
                                Organization
                            </legend>
                            
                            <div class="row g-4">
                                <div class="col-md-4  col-6">
                                    <label for="websiteSelect" class="form-label fw-semibold d-flex align-items-center">
                                        <i class="fas fa-globe-americas me-2 text-muted"></i>
                                        Website 
                                        <span class="text-danger ms-1" aria-label="required">*</span>
                                    </label>
                                    <select id="websiteSelect" 
                                            name="website_id" 
                                            class="form-select form-select-lg @error('website_id') is-invalid @enderror" 
                                            required
                                            data-placeholder="-- Select Website --"
                                            aria-describedby="websiteHelp">
                                        <option value="">-- Select Website --</option>
                                        @foreach($websites as $website)
                                        <option value="{{ $website->id }}" 
                                                {{ (old('website_id', $page->website_id ?? '') == $website->id) ? 'selected' : '' }}>
                                            {{ $website->website_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <div id="websiteHelp" class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Choose the website this page belongs to
                                    </div>
                                    @error('website_id')
                                        <div class="invalid-feedback">
                                            <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4 col-6">
                                    <label for="categorySelect" class="form-label fw-semibold d-flex align-items-center">
                                        <i class="fas fa-tags me-2 text-muted"></i>
                                        Categories
                                        <span class="badge bg-info ms-2">Optional</span>
                                    </label>
                                    <select id="categorySelect" 
                                            name="categories[]" 
                                            class="form-select form-select-lg @error('categories') is-invalid @enderror" 
                                            multiple
                                            data-placeholder="Select relevant categories for this page"
                                            aria-describedby="categoryHelp">
                                        @foreach($categories as $category)
                                        <option value="{{ $category->name }}" 
                                                {{ (isset($page) && in_array($category->name, explode(',', $page->categories ?? ''))) ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <div id="categoryHelp" class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Select or type to add custom categories
                                    </div>
                                    @error('categories')
                                        <div class="invalid-feedback">
                                            <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="border-0 mt-4">
                            <legend class="h5 mb-0 text-primary d-flex align-items-center">
                                <i class="fas fa-pen-fancy me-2"></i>
                                Content
                            </legend>
                            
                            <div class="mb-3">
                                <label for="editor" class="form-label fw-semibold d-flex align-items-center">
                                    <i class="fas fa-edit me-2 text-muted"></i>
                                    Page Content
                                </label>
                                <div class="editor-container position-relative">
                                    <textarea id="editor" 
                                              name="content" 
                                              class="form-control @error('content') is-invalid @enderror"
                                              aria-describedby="editorHelp">{{ old('content', $page->content ?? '') }}</textarea>
                                    <div id="editorHelp" class="form-text">
                                        <i class="fas fa-magic me-1"></i>
                                        Use the rich text editor to create beautiful content with images, tables, and more
                                    </div>
                                    @error('content')
                                        <div class="invalid-feedback">
                                            <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="border-0 mt-4">
                            <legend class="h5 mb-0 text-primary d-flex align-items-center">
                                <i class="fas fa-image me-2"></i>
                                Featured Image
                            </legend>
                            
                            <div class="row g-4 align-items-center">
                                <div class="col-md-6">
                                    <label for="imageInput" class="form-label fw-semibold d-flex align-items-center">
                                        <i class="fas fa-upload me-2 text-muted"></i>
                                        Upload Image
                                        <span class="badge bg-info ms-2">Optional</span>
                                    </label>
                                    <input type="file" 
                                           name="image" 
                                           id="imageInput" 
                                           accept="image/jpeg,image/jpg,image/png,image/webp" 
                                           class="form-control form-control-lg @error('image') is-invalid @enderror"
                                           aria-describedby="imageHelp">
                                    <div id="imageHelp" class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        <strong>Formats:</strong> JPG, PNG, WebP &nbsp;
                                        <strong>Max size:</strong> 2MB &nbsp;
                                        <strong>Recommended:</strong> 1200x630px
                                    </div>
                                    @error('image')
                                        <div class="invalid-feedback">
                                            <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="text-center">
                                        <div class="image-preview-container d-inline-block">
                                            <img id="imagePreview" 
                                                 src="{{ isset($page) && $page->image ? asset('storage/websites/'.$page->website_id.'/'.$page->image) : asset('assets/images/placeholder.png') }}"
                                                 class="img-thumbnail rounded-3 shadow-sm" 
                                                 style="max-width: 200px; max-height: 200px; object-fit: cover;"
                                                 alt="Page featured image preview"
                                                 loading="lazy">
                                            <div class="mt-3">
                                                <button type="button" 
                                                        id="removeImage" 
                                                        class="btn btn-outline-danger btn-sm" 
                                                        style="display: {{ (isset($page) && $page->image) ? 'inline-block' : 'none' }};">
                                                    <i class="fas fa-trash-alt me-1"></i>Remove Image
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <div class="bg-light border-top mt-4 p-4">
                            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-center gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="saveAsDraft" 
                                               name="is_draft" 
                                               value="1"
                                               {{ old('is_draft', $page->is_draft ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="saveAsDraft">
                                            <i class="fas fa-save me-1"></i>Save as draft
                                        </label>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Drafts are not visible to the public
                                    </small>
                                </div>
                                
                                <div class="btn-toolbar gap-2" role="toolbar">
                                    <a href="{{ route('pages.index') }}" 
                                       class="btn btn-outline-secondary btn-lg px-4">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                    @if(isset($page))
                                    <a href="{{ route('pages.show', $page->id) }}" 
                                       class="btn btn-outline-info btn-lg px-4" 
                                       target="_blank">
                                        <i class="fas fa-eye me-2"></i>Preview
                                    </a>
                                    @endif
                                    <button type="submit" class="btn btn-primary btn-lg px-5" id="submitBtn">
                                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                                        <i class="fas fa-save me-2"></i>
                                        {{ isset($page) ? 'Update Page' : 'Create Page' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="unsavedChangesModal" tabindex="-1" aria-labelledby="unsavedChangesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-dark border-0">
                <h5 class="modal-title d-flex align-items-center" id="unsavedChangesModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Unsaved Changes
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-question-circle fa-2x text-warning me-3"></i>
                    </div>
                    <div class="flex-grow-1">
                        <p class="mb-2">You have unsaved changes that will be lost.</p>
                        <p class="mb-0 text-muted small">Are you sure you want to leave this page?</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-arrow-left me-1"></i>Stay on Page
                </button>
                <button type="button" class="btn btn-danger" id="confirmLeave">
                    <i class="fas fa-sign-out-alt me-1"></i>Leave Page
                </button>
            </div>
        </div>
    </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="autoSaveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i class="fas fa-save text-success me-2"></i>
            <strong class="me-auto">Auto-save</strong>
            <small class="text-muted">just now</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            Your changes have been automatically saved.
        </div>
    </div>
</div>
@endsection