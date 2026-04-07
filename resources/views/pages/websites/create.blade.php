@extends('layout.default')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
<link href="/assets/css/AllPagesCreate.css" rel="stylesheet" />
<style>
    /* Optional: Fix file input spacing */
    input[type="file"] {
        padding: 6px 12px;
    }

    /* Center images nicely */
    .preview-img {
        max-width: 150px;
        border-radius: 8px;
        object-fit: contain;
    }

    .favicon-img {
        max-width: 32px;
        border-radius: 4px;
        object-fit: contain;
    }

    /* Customize buttons for consistency */
    .btn-theme {
        background-color: #007bff;
        border-color: #007bff;
        color: #fff;
    }

    .btn-theme:hover {
        background-color: #0056b3;
        border-color: #0056b3;
        color: #fff;
    }

    /* Validation styles for Select2 visible input */
    .select2-selection.is-invalid {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.5) !important;
        background-color: #f8d7da !important;
        border-radius: 0.375rem !important;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .select2-selection.is-valid {
        border-color: #198754 !important;
        box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.5) !important;
        background-color: #d1e7dd !important;
        border-radius: 0.375rem !important;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
</style>
@endpush

@section('content')
<div class="container mt-5 mb-5">
    <!-- Flash Messages -->
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header bg-primary text-white text-center py-3">
            <h3 class="mb-0">{{ isset($website) ? 'Edit Website' : 'Create Website' }}</h3>
        </div>

        <div class="card-body p-4">
            <form
                action="{{ isset($website) ? route('websites.update', $website->id) : route('websites.store') }}"
                method="POST" enctype="multipart/form-data"
                class="needs-validation" novalidate>
                @csrf
                @isset($website)
                @method('PUT')
                @endisset

                <div class="row g-3">
                    <!-- Website Name -->
                    <div class="col-12 col-md-6">
                        <label for="website_name" class="form-label fw-semibold">Website Name <span class="text-danger">*</span></label>
                        <input id="website_name" type="text" name="website_name" value="{{ old('website_name', $website->website_name ?? '') }}" class="form-control" required>
                        @error('website_name') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <!-- Domain Name -->
                    <div class="col-12 col-md-6">
                        <label for="domain" class="form-label fw-semibold">Domain Name <span class="text-danger">*</span></label>
                        <input id="domain" type="text" name="domain" value="{{ old('domain', $website->domain ?? '') }}" class="form-control" required>
                        @error('domain') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <!-- Theme Selection -->
                    <div class="col-12 col-md-6">
                        <label for="website_theme" class="form-label fw-semibold">Select Theme <span class="text-danger">*</span></label>
                        <select id="website_theme" name="website_theme" class="form-select select2" required>
                            <option value="" disabled {{ old('website_theme', $website->website_theme ?? '') ? '' : 'selected' }}>-- Select a Theme --</option>
                            @foreach($themes as $theme)
                            <option value="{{ $theme->themename }}" {{ (old('website_theme', $website->website_theme ?? '') == $theme->themename) ? 'selected' : '' }}>
                                {{ $theme->themename }}
                            </option>
                            @endforeach
                        </select>
                        @error('website_theme') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <!-- Website Type -->
                    <div class="col-12 col-md-3">
                        <label for="website_type" class="form-label fw-semibold small">
                            Website Type <span class="text-danger">*</span>
                        </label>
                        <select
                            id="website_type"
                            name="website_type"
                            class="form-select form-select-sm select2"
                            style="width: auto; min-width: 80px;"
                            required>
                            <option value="" disabled {{ old('website_type', $website->website_type ?? '') ? '' : 'selected' }}>
                                -- Select Website Type --
                            </option>
                            <option value="blog" {{ old('website_type', $website->website_type ?? '') === 'blog' ? 'selected' : '' }}>
                                Blog
                            </option>
                            <option value="quiz" {{ old('website_type', $website->website_type ?? '') === 'quiz' ? 'selected' : '' }}>
                                Quiz
                            </option>
                        </select>
                        @error('website_type')
                        <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>



                    <!-- Vertical Selection -->
                    <div class="col-12 col-md-3">
                        <label for="website_vertical" class="form-label fw-semibold">Select Vertical <span class="text-danger">*</span></label>
                        <select id="website_vertical" name="website_vertical" class="form-select select2" required>
                            <option value="" disabled {{ old('website_vertical', $website->website_vertical ?? '') ? '' : 'selected' }}>-- Select a Vertical --</option>
                            @foreach($verticals as $vertical)
                            <option value="{{ $vertical->name }}" {{ (old('website_vertical', $website->website_vertical ?? '') == $vertical->name) ? 'selected' : '' }}>
                                {{ $vertical->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('website_vertical') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <!-- Company Name -->
                    <div class="col-12 col-md-6">
                        <label for="company_name" class="form-label fw-semibold">Company Name</label>
                        <input id="company_name" type="text" name="company_name" value="{{ old('company_name', $website->company_name ?? '') }}" class="form-control">
                        @error('company_name') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <!-- Company Address -->
                    <div class="col-12 col-md-6">
                        <label for="company_address" class="form-label fw-semibold">Company Address</label>
                        <textarea id="company_address" name="company_address" rows="3" class="form-control">{{ old('company_address', $website->company_address ?? '') }}</textarea>
                        @error('company_address') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <!-- Email -->
                    <div class="col-12 col-md-6">
                        <label for="email" class="form-label fw-semibold">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $website->email ?? '') }}" class="form-control">
                        @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <!-- Contact -->
                    <div class="col-12 col-md-6">
                        <label for="contact" class="form-label fw-semibold">Contact</label>
                        <input id="contact" type="text" name="contact" value="{{ old('contact', $website->contact ?? '') }}" class="form-control">
                        @error('contact') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <!-- Logo Upload -->
                    <div class="col-12 col-md-6">
                        <label for="logo_path" class="form-label fw-semibold">Upload Logo</label>
                        <input id="logo_path" type="file" name="logo_path" class="form-control">
                        @if(isset($website) && $website->logo_path)
                        <div class="mt-3 d-flex justify-content-center">
                            <img src="{{ asset('storage/website-logo/' . $website->id . '/' .$website->logo_path) }}" alt="Logo Preview" class="preview-img img-thumbnail" />
                        </div>
                        @endif
                        @error('logo_path') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <!-- Favicon Upload -->
                    <div class="col-12 col-md-6">
                        <label for="favicon_path" class="form-label fw-semibold">Upload Favicon</label>
                        <input id="favicon_path" type="file" name="favicon_path" class="form-control">
                        @if(isset($website) && $website->favicon_path)
                        <div class="mt-3 d-flex justify-content-center">
                            <img src="{{ asset('storage/website-logo/' . $website->id . '/' .$website->favicon_path) }}" alt="Favicon Preview" class="favicon-img img-thumbnail" />
                        </div>
                        @endif
                        @error('favicon_path') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-center mt-4 gap-3">
                    <button type="submit" class="btn btn-theme btn-lg px-5">
                        {{ isset($website) ? 'Update' : 'Save' }}
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-lg px-5" onclick="window.location.href='{{ route('websites.index') }}'">
                        Cancel
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endSection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%',
            placeholder: 'Select an option',
            allowClear: true,
        });

        function validateSelect2(select) {
            const val = $(select).val();
            const container = $(select).next('.select2-container');
            const selection = container.find('.select2-selection');

            if (!val || val.length === 0) {
                $(select).addClass('is-invalid').removeClass('is-valid');
                selection.addClass('is-invalid').removeClass('is-valid');
            } else {
                $(select).removeClass('is-invalid').addClass('is-valid');
                selection.removeClass('is-invalid').addClass('is-valid');
            }
        }


        // Listen to Select2 events to validate on change/unselect
        $('.select2').on('select2:select select2:unselect', function() {
            validateSelect2(this);
        });

        // Also validate on form submit
        $('.needs-validation').on('submit', function(e) {
            let valid = true;
            $(this).find('select[required]').each(function() {
                validateSelect2(this);
                if (!$(this).val() || $(this).val().length === 0) {
                    valid = false;
                }
            });

            if (!valid) {
                e.preventDefault();
                e.stopPropagation();
            }
            $(this).addClass('was-validated');
        });
    });
</script>
@endpush