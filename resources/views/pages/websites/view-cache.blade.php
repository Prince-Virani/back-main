@extends('layout.default')

@section('title', "Cache for {$website->website_name}")

@push('styles')
{{-- Highlight.js theme --}}
<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/styles/github-dark.min.css" />
@endpush

@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow-lg">
        <div
            class="card-header bg-primary text-white py-3 
             d-flex flex-wrap justify-content-between align-items-center">
            <div class="d-flex align-items-center mb-2 mb-md-0">
                <i class="fa fa-database fa-lg me-2"></i>
                <div>
                    <h5 class="mb-0">
                        Cache Data for <strong>{{ $website->website_name }}</strong>
                    </h5>
                    <small class="text-light">{{ $website->domain }}</small>
                </div>
            </div>
            <div>
                <button
                    type="button"
                    class="btn btn-sm btn-light me-2"
                    data-bs-toggle="collapse"
                    data-bs-target="#jsonCollapse"
                    aria-expanded="true"
                    aria-controls="jsonCollapse">
                    <i class="fa fa-eye"></i> Toggle JSON
                </button>
                <a
                    href="{{ route('websites.index') }}"
                    class="btn btn-sm btn-light">
                    <i class="fa fa-arrow-left"></i> Back to Websites
                </a>
            </div>
        </div>

        <div id="jsonCollapse" class="collapse show">
            <div class="card-body p-0">
                <pre
                    class="m-0 p-3 bg-dark text-white text-break"
                    style="max-height:70vh; overflow:auto; font-size:.9rem;"><code id="jsonCode" class="language-json">{{ json_encode($cacheData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/highlight.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('pre code').forEach(block => {
            hljs.highlightElement(block);
        });
    });
</script>
@endpush