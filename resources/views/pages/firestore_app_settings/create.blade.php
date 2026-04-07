@extends('layout.default')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
<link href="/assets/css/AllPagesCreate.css" rel="stylesheet" />
<style>
    input[type="file"] { padding: 6px 12px; }
    .preview-img { max-width: 150px; border-radius: 8px; object-fit: contain; }
    .favicon-img { max-width: 32px; border-radius: 4px; object-fit: contain; }
    .btn-theme { background-color:#007bff; border-color:#007bff; color:#fff; }
    .btn-theme:hover { background-color:#0056b3; border-color:#0056b3; color:#fff; }
    .select2-selection.is-invalid{border-color:#dc3545!important;box-shadow:0 0 0 .25rem rgba(220,53,69,.5)!important;background-color:#f8d7da!important;border-radius:.375rem!important;transition:border-color .15s,box-shadow .15s}
    .select2-selection.is-valid{border-color:#198754!important;box-shadow:0 0 0 .25rem rgba(25,135,84,.5)!important;background-color:#d1e7dd!important;border-radius:.375rem!important;transition:border-color .15s,box-shadow .15s}
</style>
@endpush

@section('content')
@php
  $doc     = $firestore_setting ?? null;
  $isEdit  = (bool) $doc;
  $hasCreds = !empty($doc?->credentials_filename);
@endphp

<div class="container mt-5 mb-5">
  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('error') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="card shadow-lg border-0 rounded-4">
    <div class="card-header bg-primary text-white text-center py-3">
      <h3 class="mb-0">{{ $isEdit ? 'Edit Firestore App Setting' : 'Create Firestore App Setting' }}</h3>
    </div>
    <div class="card-body p-4">
      <form action="{{ $isEdit ? route('firestore-app-settings.update', $doc->id) : route('firestore-app-settings.store') }}"
            method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="row g-3">
          <div class="col-12 col-md-6">
            <label for="application_package" class="form-label fw-semibold">Select Application <span class="text-danger">*</span></label>
            <select id="application_package" name="application_package" class="form-select select2" required>
              <option value="" disabled {{ old('application_package', $doc->application_package ?? '') ? '' : 'selected' }}>-- Select Application --</option>
              @foreach($apps as $app)
                <option value="{{ $app->package_name }}" {{ old('application_package', $doc->application_package ?? '') === $app->package_name ? 'selected' : '' }}>
                  {{ $app->package_name }} ({{ $app->application_name }})
                </option>
              @endforeach
            </select>
            @error('application_package')<div class="text-danger small">{{ $message }}</div>@enderror
          </div>

          <div class="col-12 col-md-6">
            <label for="firebase_project_id" class="form-label fw-semibold">FIREBASE_PROJECT_ID <span class="text-danger">*</span></label>
            <input id="firebase_project_id" type="text" name="firebase_project_id"
                   value="{{ old('firebase_project_id', $doc->firebase_project_id ?? '') }}" class="form-control" required>
            @error('firebase_project_id')<div class="text-danger small">{{ $message }}</div>@enderror
          </div>

          <div class="col-12 col-md-6">
            <label for="collection_name" class="form-label fw-semibold">Firestore Collection <span class="text-danger">*</span></label>
            <input id="collection_name" type="text" name="collection_name"
                   value="{{ old('collection_name', $doc->collection_name ?? '') }}" class="form-control" required>
            @error('collection_name')<div class="text-danger small">{{ $message }}</div>@enderror
          </div>

          <div class="col-12 col-md-6">
            <label for="document_name" class="form-label fw-semibold">Document Name <span class="text-danger">*</span></label>
            <input id="document_name" type="text" name="document_name"
                   value="{{ old('document_name', $doc->document_name ?? '') }}" class="form-control" required>
            @error('document_name')<div class="text-danger small">{{ $message }}</div>@enderror
          </div>

          <!-- New Field Name -->
          <div class="col-12 col-md-6">
            <label for="field_name" class="form-label fw-semibold">Field Name <span class="text-danger">*</span></label>
            <input id="field_name" type="text" name="field_name"
                   value="{{ old('field_name', $doc->field_name ?? '') }}" class="form-control" required>
            @error('field_name')<div class="text-danger small">{{ $message }}</div>@enderror
          </div>

          <!-- Service Account (JSON) -->
          <div class="col-12 col-md-6">
            <label for="credentials_file" class="form-label fw-semibold">
              {{ $isEdit ? 'Replace Service Account (JSON)' : 'Service Account (JSON)' }}
              @if(!$isEdit || !$hasCreds)
                <span class="text-danger">*</span>
              @endif
            </label>
            <input id="credentials_file" type="file" name="credentials_file" class="form-control" accept="application/json,.json"
                   @if(!$isEdit || !$hasCreds) required @endif>
            @if($isEdit && $hasCreds)
              <div class="mt-2">
                <small class="text-muted d-block">
                  Current file: <code>{{ $doc->credentials_filename }}</code>
                </small>
                <div class="form-text">Leave blank to keep the existing credentials.</div>
              </div>
            @endif
            @error('credentials_file')<div class="text-danger small">{{ $message }}</div>@enderror
          </div>

          <div class="col-12 col-md-6 d-flex align-items-end">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="status_flag" id="status_flag"
                     {{ old('status_flag', $doc->status_flag ?? true) ? 'checked' : '' }}>
              <label class="form-check-label" for="status_flag">Active</label>
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-center mt-4 gap-3">
          <button type="submit" class="btn btn-theme btn-lg">{{ $isEdit ? 'Update' : 'Save' }}</button>
          <a href="{{ route('firestore-app-settings.index') }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script>
  $(document).ready(function() {
    $('.select2').select2({ width:'100%', placeholder:'Select an option', allowClear:true });

    function validateSelect2(select) {
      const val = $(select).val();
      const selection = $(select).next('.select2-container').find('.select2-selection');
      if (!val || val.length === 0) {
        $(select).addClass('is-invalid').removeClass('is-valid');
        selection.addClass('is-invalid').removeClass('is-valid');
      } else {
        $(select).removeClass('is-invalid').addClass('is-valid');
        selection.removeClass('is-invalid').addClass('is-valid');
      }
    }

    $('.select2').on('select2:select select2:unselect', function() { validateSelect2(this); });

    $('.needs-validation').on('submit', function(e) {
      let valid = true;
      $(this).find('select[required], input[required]').each(function() {
        if (!$(this).val() || $(this).val().length === 0) {
          $(this).addClass('is-invalid').removeClass('is-valid');
          valid = false;
        } else {
          $(this).removeClass('is-invalid').addClass('is-valid');
        }
      });
      if (!valid) { e.preventDefault(); e.stopPropagation(); }
      $(this).addClass('was-validated');
    });
  });
</script>
@endpush
