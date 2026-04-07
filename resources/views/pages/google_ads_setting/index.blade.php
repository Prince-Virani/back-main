@extends('layout.default')

@section('title', 'Google Ads Settings')

@section('content')
    <div class="d-flex align-items-center mb-3">
        <div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Google Ads</a></li>
                <li class="breadcrumb-item active">All Settings</li>
            </ul>
            <h1 class="page-header mb-0">🛠️ Manage Google Ads Settings</h1>
        </div>
        <div class="ms-auto">
            @can('add-google-ads-settings')
                <a href="{{ route('google-settings.create') }}" class="btn btn-theme">
                    <i class="fa fa-plus-circle fa-fw me-1"></i> Add Setting
                </a>
            @endcan
            <a href="{{ route('dashboard') }}" class="btn btn-secondary ms-2">
                <i class="fa fa-home fa-fw me-1"></i> Go to Dashboard
            </a>
        </div>
    </div>

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="table-responsive p-4">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Website</th>
                        <th>Network Code</th>
                        <th>AdSense Name</th>
                        <th>ADX Name</th>
                        <!-- <th>GA Run ID</th>
                            <th>GA Order ID</th>
                            <th>Advertiser ID</th>
                            <th>Custom Key ID</th>
                            <th>Web Property</th>
                            <th>GA4 Account</th> -->
                        <!-- <th>Credentials</th> -->
                        <th>Status</th>
                        <th>Edit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($settings as $key => $s)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $s->website->website_name }}</td>
                            <td>{{ $s->network_code }}</td>
                            <td>{{ $s->google_adsense_name }}</td>
                            <td>{{ $s->adx_name }}</td>
                            <!-- <td>{{ $s->ga_ad_unit_run_id }}</td>
                                <td>{{ $s->ga_order_id }}</td>
                                <td>{{ $s->ga_advertiser_id }}</td>
                                <td>{{ $s->ga_custom_targeting_key_id }}</td>
                                <td>{{ $s->ga_web_property }}</td>
                                <td>{{ $s->ga4_account_id }}</td> -->
                            <!-- <td>
                                    @if ($s->credentials_path)
    <a href="{{ asset('storage/credentials/' . $s->credentials_path) }}" target="_blank">Download</a>
@else
    —
    @endif
                                </td> -->
                            <td>{{ $s->status ? 'Active' : 'Inactive' }}</td>
                            <td>
                                @can('edit-google-ads-settings')
                                    <a href="{{ route('google-settings.edit', $s->id) }}" class="btn btn-theme btn-sm">
                                        ✏️ Edit
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="text-center text-muted">
                                😔 No settings found. Click "Add Setting" to begin.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="d-flex justify-content-center mt-3">
                {{ $settings->links() }}
            </div>
        </div>
    </div>
@endsection
