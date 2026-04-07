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
    $isEdit   = isset($google_setting);
    $hasCreds = !empty($google_setting->credentials_path ?? null);
@endphp

<div class="container mt-5 mb-5">
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
            <h3 class="mb-0">
                {{ $isEdit ? 'Edit Google Ads Settings' : 'Create Google Ads Settings' }}
            </h3>
        </div>
        <div class="card-body p-4">
            <form
              action="{{ $isEdit ? route('google-settings.update', $google_setting->id) : route('google-settings.store') }}"
              method="POST"
              enctype="multipart/form-data"
              class="needs-validation"
              novalidate
            >
                @csrf
                @isset($google_setting) @method('PUT') @endisset

                <div class="row g-3">
                    <!-- Select Website -->
                    <div class="col-12 col-md-6">
                        <label for="website_id" class="form-label fw-semibold">Select Website <span class="text-danger">*</span></label>
                        <select id="website_id" name="website_id" class="form-select select2" required>
                            <option value="" disabled {{ old('website_id', $google_setting->website_id ?? '') ? '' : 'selected' }}>-- Select Website --</option>
                            @foreach($websites as $id => $name)
                                <option value="{{ $id }}" {{ old('website_id', $google_setting->website_id ?? '') == $id ? 'selected' : '' }}>
                                  {{ $name }}
                                </option>
                            @endforeach
                        </select>
                        @error('website_id')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <!-- Google AdSense Name -->
                    <div class="col-12 col-md-6">
                        <label for="google_adsense_name" class="form-label fw-semibold">Google AdSense Name <span class="text-danger">*</span></label>
                        <input id="google_adsense_name" type="text" name="google_adsense_name"
                               value="{{ old('google_adsense_name', $google_setting->google_adsense_name ?? '') }}"
                               class="form-control" required>
                        @error('google_adsense_name')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <!-- ADX Name -->
                    <div class="col-12 col-md-6">
                        <label for="adx_name" class="form-label fw-semibold">ADX Name <span class="text-danger">*</span></label>
                        <input id="adx_name" type="text" name="adx_name"
                               value="{{ old('adx_name', $google_setting->adx_name ?? '') }}"
                               class="form-control" required>
                        @error('adx_name')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <!-- Network Code -->
                    <div class="col-12 col-md-6">
                        <label for="network_code" class="form-label fw-semibold">Network Code <span class="text-danger">*</span></label>
                        <input id="network_code" type="text" name="network_code"
                               value="{{ old('network_code', $google_setting->network_code ?? '') }}"
                               class="form-control" required>
                        @error('network_code')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <!-- Status -->
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" value="1"
                              {{ old('status', $google_setting->status ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>

                    <!-- Upload Credentials JSON -->
                    <div class="col-12 col-md-6">
                        <label for="credentials_file" class="form-label fw-semibold">
                            Upload Credentials JSON
                            @if(!$isEdit || !$hasCreds)
                                <span class="text-danger">*</span>
                            @endif
                        </label>
                        <input id="credentials_file" type="file" name="credentials_file" class="form-control"
                               @if(!$isEdit || !$hasCreds) required @endif>
                        @if($isEdit && $hasCreds)
                            <div class="mt-2">
                                <a href="{{ asset('storage/credentials/'.$google_setting->credentials_path) }}" target="_blank">Current File</a>
                                <div class="form-text">Leave blank to keep the existing credentials.</div>
                            </div>
                        @endif
                        @error('credentials_file')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <!-- GA_AD_UNIT_RUN_ID -->
                    <div class="col-12 col-md-6">
                        <label for="ga_ad_unit_run_id" class="form-label fw-semibold">GA_AD_UNIT_RUN_ID <span class="text-danger">*</span></label>
                        <input id="ga_ad_unit_run_id" type="text" name="ga_ad_unit_run_id"
                               value="{{ old('ga_ad_unit_run_id', $google_setting->ga_ad_unit_run_id ?? '') }}"
                               class="form-control" required>
                        @error('ga_ad_unit_run_id')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <!-- GA_ORDER_ID -->
                    <div class="col-12 col-md-6">
                        <label for="ga_order_id" class="form-label fw-semibold">GA_ORDER_ID <span class="text-danger">*</span></label>
                        <input id="ga_order_id" type="text" name="ga_order_id"
                               value="{{ old('ga_order_id', $google_setting->ga_order_id ?? '') }}"
                               class="form-control" required>
                        @error('ga_order_id')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <!-- GA_ADVERTISER_ID -->
                    <div class="col-12 col-md-6">
                        <label for="ga_advertiser_id" class="form-label fw-semibold">GA_ADVERTISER_ID <span class="text-danger">*</span></label>
                        <input id="ga_advertiser_id" type="text" name="ga_advertiser_id"
                               value="{{ old('ga_advertiser_id', $google_setting->ga_advertiser_id ?? '') }}"
                               class="form-control" required>
                        @error('ga_advertiser_id')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <!-- GA_CUSTOM_TARGETING_KEY_ID -->
                    <div class="col-12 col-md-6">
                        <label for="ga_custom_targeting_key_id" class="form-label fw-semibold">GA_CUSTOM_TARGETING_KEY_ID <span class="text-danger">*</span></label>
                        <input id="ga_custom_targeting_key_id" type="text" name="ga_custom_targeting_key_id"
                               value="{{ old('ga_custom_targeting_key_id', $google_setting->ga_custom_targeting_key_id ?? '') }}"
                               class="form-control" required>
                        @error('ga_custom_targeting_key_id')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <!-- GA_WEB_PROPERTY -->
                    <div class="col-12 col-md-6">
                        <label for="ga_web_property" class="form-label fw-semibold">GA_WEB_PROPERTY <span class="text-danger">*</span></label>
                        <input id="ga_web_property" type="text" name="ga_web_property"
                               value="{{ old('ga_web_property', $google_setting->ga_web_property ?? '') }}"
                               class="form-control" required>
                        @error('ga_web_property')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <!-- GA4_ACCOUNT_ID -->
                    <div class="col-12 col-md-6">
                        <label for="ga4_account_id" class="form-label fw-semibold">GA4_ACCOUNT_ID <span class="text-danger">*</span></label>
                        <input id="ga4_account_id" type="text" name="ga4_account_id"
                               value="{{ old('ga4_account_id', $google_setting->ga4_account_id ?? '') }}"
                               class="form-control" required>
                        @error('ga4_account_id')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex justify-content-center mt-4 gap-3">
                    <button type="submit" class="btn btn-theme btn-lg">
                        {{ $isEdit ? 'Update' : 'Save' }}
                    </button>
                    <a href="{{ route('google-settings.index') }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
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
            $(this).find('select[required]').each(function() {
                validateSelect2(this);
                if (!$(this).val() || $(this).val().length === 0) valid = false;
            });
            if (!valid) { e.preventDefault(); e.stopPropagation(); }
            $(this).addClass('was-validated');
        });
    });
</script>
@endpush
