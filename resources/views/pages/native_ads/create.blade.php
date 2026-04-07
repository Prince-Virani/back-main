{{-- resources/views/native_ads/create.blade.php --}}
@extends('layout.default')

@push('css')

<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />

<link href="/assets/css/AllPagesCreate.css" rel="stylesheet" />

<style>
  /* Fix file input spacing */
  input[type="file"] {
    padding: 6px 12px;
  }

  /* Image previews */
  .preview-img {
    max-width: 150px;
    border-radius: 8px;
    object-fit: contain;
    display: none;
    margin-top: 0.5rem;
  }

  /* Select2 validation styling */
  .select2-selection.is-invalid {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.5) !important;
    background-color: #f8d7da !important;
    border-radius: 0.375rem !important;
  }

  .select2-selection.is-valid {
    border-color: #198754 !important;
    box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.5) !important;
    background-color: #d1e7dd !important;
    border-radius: 0.375rem !important;
  }
</style>
@endpush

@section('content')
<div class="container mt-4 mb-5">
  {{-- Flash Messages --}}
  @if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif
  @if(session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif

  <div class="card shadow-lg border-0 rounded-4">
    <div class="card-header bg-primary text-white text-center py-3">
      <h3 class="mb-0">Create Native Ad</h3>
    </div>
    <div class="card-body position-relative p-4">
      <div id="loadingOverlay" style="display:none; position:absolute; top:0; left:0;
             width:100%; height:100%; background:rgba(255,255,255,0.8); z-index:10;
             align-items:center; justify-content:center;">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Uploading...</span>
        </div>
      </div>

      <form id="nativeAdForm"
        action="{{ route('native_ads.store') }}"
        method="POST"
        enctype="multipart/form-data"
        class="needs-validation"
        novalidate>
        @csrf

        <div class="row g-3">
          {{-- Application --}}
          <div class="col-12 col-md-6">
            <label class="form-label fw-semibold">Application <span class="text-danger">*</span></label>
            <select name="packagename"
              class="form-select select2 @error('packagename') is-invalid @enderror"
              required>
              <option value="" disabled selected>— Select Application —</option>
              @foreach($applications as $app)
              <option value="{{ $app->package_name }}"
                {{ old('packagename') === $app->package_name ? 'selected' : '' }}>
                {{ $app->application_name }}
              </option>
              @endforeach
            </select>
            @error('packagename')
            <div class="text-danger small">{{ $message }}</div>
            @enderror
          </div>

          {{-- Call To Action Link --}}
          <div class="col-12 col-md-6">
            <label class="form-label fw-semibold">Call To Action Link <span class="text-danger">*</span></label>
            <input type="url"
              name="calltoactionlink"
              value="{{ old('calltoactionlink') }}"
              class="form-control @error('calltoactionlink') is-invalid @enderror"
              required>
            @error('calltoactionlink')
            <div class="text-danger small">{{ $message }}</div>
            @enderror
          </div>

          {{-- Media Image --}}
          <div class="col-12 col-md-6">
            <label class="form-label fw-semibold">Media Image <span class="text-danger">*</span></label>
            <input type="file"
              name="media"
              accept="image/*"
              class="form-control @error('media') is-invalid @enderror"
              onchange="previewImage(this,'mediaPreview')"
              required>
            @error('media') <div class="text-danger small">{{ $message }}</div>@enderror
            <img id="mediaPreview" class="preview-img img-thumbnail" alt="Media Preview">
          </div>

          {{-- Icon Image --}}
          <div class="col-12 col-md-6">
            <label class="form-label fw-semibold">Icon Image <span class="text-danger">*</span></label>
            <input type="file"
              name="icon"
              accept="image/*"
              class="form-control @error('icon') is-invalid @enderror"
              onchange="previewImage(this,'iconPreview')"
              required>
            @error('icon') <div class="text-danger small">{{ $message }}</div>@enderror
            <img id="iconPreview" class="preview-img img-thumbnail" alt="Icon Preview">
          </div>

          {{-- Title --}}
          <div class="col-12 col-md-6">
            <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
            <input type="text"
              name="title"
              value="{{ old('title') }}"
              class="form-control @error('title') is-invalid @enderror"
              required>
            @error('title')
            <div class="text-danger small">{{ $message }}</div>
            @enderror
          </div>

          {{-- Button Text --}}
          <div class="col-12 col-md-6">
            <label class="form-label fw-semibold">Button Text <span class="text-danger">*</span></label>
            <input type="text"
              name="buttontext"
              value="{{ old('buttontext') }}"
              class="form-control @error('buttontext') is-invalid @enderror"
              required>
            @error('buttontext')
            <div class="text-danger small">{{ $message }}</div>
            @enderror
          </div>

          {{-- Description --}}
          <div class="col-12">
            <label class="form-label fw-semibold">Description</label>
            <textarea id="description"
              name="description"
              class="form-control">{{ old('description') }}</textarea>
            @error('description')
            <div class="text-danger small">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="d-flex justify-content-center mt-4">
          <button type="button"
            class="btn btn-theme btn-lg px-5"
            onclick="submitNativeAd()">
            Save Native Ad
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('js')
<script>
  window.nativeAdsIndexUrl = "{{ route('native_ads.index') }}";
</script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script src="/assets/js/native-ad.js?ts=<?= time() ?>"></script>

@endpush