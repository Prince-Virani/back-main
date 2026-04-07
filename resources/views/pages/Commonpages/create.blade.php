@extends('layout.default')
@push('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-lite.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
<link href="/assets/css/AllPagesCreate.css" rel="stylesheet" />

@endpush
@push('js')
<!-- Include Summernote & Select Picker  JS -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-lite.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        $('#editor').summernote({
            height: 300,
            placeholder: 'Write your content here...',
            toolbar: [
         //[groupname, [button list]]
          ['style', ['style']],
          ['fontsize', ['fontsize']],
         //['font', ['font']],
          ['style', ['bold', 'italic', 'underline']],
          ['fontname', ['fontname']],
          ['color', ['color']],
          ['para', ['ul', 'ol', 'paragraph']],
          ['height', ['height']],
          ['table', ['table']],
          ['insert', ['link', 'picture', 'hr']],
          ['view', ['fullscreen', 'codeview']],
          ['help', ['help']]
       ]

        });
        $('#websiteSelect').select2({
            placeholder: "Select a Website",
            allowClear: true
        });
       
    });
</script>
@endpush

@section('content')
<div class="container mt-5">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header bg-primary text-white text-center py-3">
            <h3 class="mb-0">{{ isset($Commonpage) ? 'Edit Common Page' : 'Create Common Page' }}</h3>
        </div>

        <div class="card-body p-4">
            <form action="{{ isset($Commonpage) ? route('Commonpages.update', $Commonpage->id) : route('Commonpages.store') }}" 
                  method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                @csrf
                @if(isset($Commonpage))
                    @method('PUT') {{-- Laravel requires this for updating --}}
                @endif

                <!-- Page Name -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Page Name:</label>
                    <input type="text" name="page_name" id="page_name" value="{{ old('page_name', $Commonpage->page_name ?? '') }}" 
                           class="form-control shadow-sm" required>
                           @error('page_name')
            <div class="text-danger">{{ $message }}</div>
        @enderror
                </div>
                 <!-- Page URl -->
                 <div class="mb-4">
                    <label class="form-label fw-bold">Page URl:</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $Commonpage->slug ?? '') }}" 
                           class="form-control shadow-sm" required>
                           @error('slug')
            <div class="text-danger">{{ $message }}</div>
        @enderror
                </div>
                
                <!-- Website Selection Dropdown -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Select Website:</label>
                    <select id="websiteSelect" name="website_id" class="form-control shadow-sm" required>
                        <option value="">-- Select Website --</option>
                        @foreach($websites as $website)
                            <option value="{{ $website->id }}" 
                                {{ isset($Commonpage) && $Commonpage->website_id == $website->id ? 'selected' : '' }}>
                                {{ $website->website_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('website_id')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                

                <!-- Content -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Content:</label>
                    <textarea id="editor" name="content" class="form-control shadow-sm">{{ old('content', $Commonpage->content ?? '') }}</textarea>
                    @error('content')
            <div class="text-danger">{{ $message }}</div>
        @enderror
                </div>

                

               <!-- Buttons (Perfectly Centered) -->
                <div class="d-flex justify-content-center align-items-center mt-4">
                    <button type="submit" class="btn btn-theme btn-lg px-5 py-2 fw-bold shadow-lg me-3">
                        {{ isset($Commonpage) ? 'Update' : 'Save' }}
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-lg px-5 py-2 fw-bold shadow-lg"
                            onclick="window.location.href='{{ route('Commonpages.index') }}'">
                        Cancel
                        </button>
                </div>
            </form>
        </div>
    </div>
</div>






@endsection
