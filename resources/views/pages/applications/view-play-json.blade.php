@extends('layout.default')

@section('title', 'Play JSON for ' . ($application->title ?? $application->package_name))

@push('styles')
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
                <i class="fa fa-mobile-alt fa-lg me-2"></i> {{-- Application icon --}}
                <div>
                    <h5 class="mb-0">
                        Play JSON for <strong>{{ $application->title ?? $application->package_name }}</strong>
                    </h5>
                    <small class="text-light">{{ $application->package_name }}</small>
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
                    href="{{ route('applications.index') }}"
                    class="btn btn-sm btn-light">
                    <i class="fa fa-arrow-left"></i> Back to Applications
                </a>
            </div>
        </div>

        <div id="jsonCollapse" class="collapse show">
            <div class="card-body p-0">
                @php
                $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
                if (is_string($data)) {
                $decoded = json_decode($data, true);
                $pretty = json_last_error() === JSON_ERROR_NONE ? json_encode($decoded, $flags)
                : $data;
                } else {
                $pretty = json_encode($data, $flags);
                }
                @endphp
                <pre class="m-0 p-3 bg-dark text-white text-break" style="max-height:70vh; overflow:auto; font-size:.9rem;">
                  <code id="jsonCode" class="language-json">{{ $pretty }}</code>
                </pre>
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