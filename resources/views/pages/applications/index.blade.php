@extends('layout.default')

@section('title', 'Applications')

@section('content')
    <div class="d-flex align-items-center mb-3">
        <div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Applications</a></li>
                <li class="breadcrumb-item active">All Applications</li>
            </ul>
            <h1 class="page-header mb-0">📱 Manage Applications</h1>
        </div>
        <div class="ms-auto">
            @can('add-application')
                <a href="{{ route('applications.create') }}" class="btn btn-theme">
                    <i class="fa fa-plus-circle fa-fw me-1"></i> Add Application
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
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="tab-content p-4">
            <div class="tab-pane fade show active" id="allTab">
                <div class="table-responsive">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Application Name</th>
                                <th>Package Name</th>
                                <th>Logo</th>
                                <th>Installs</th>
                                <th>Last Updated</th>
                                <th>Privacy URL</th>
                                <th>App Version</th>
                                @can('edit-application')
                                    <th>Edit</th>
                                @endcan
                                @can('fetch-application')
                                    <th>Fetch Data</th>
                                @endcan
                                <th>View JSON</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($applications->count() > 0)
                                @foreach ($applications as $key => $application)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $application->application_name }}</td>
                                        <td>{{ $application->package_name }}</td>
                                        <td>
                                            @if ($application->icon_url)
                                                <img src="{{ $application->icon_url }}" alt="App Icon"
                                                    style="width: 50px; height: 50px; object-fit: cover;">
                                            @else
                                                <span>No Icon</span>
                                            @endif
                                        </td>
                                        <td>{{ number_format($application->installs) }}</td>
                                        <td>{{ $application->last_updated ? \Carbon\Carbon::parse($application->last_updated)->format('Y-m-d') : 'N/A' }}
                                        </td>
                                        <td>
                                            @if ($application->privacy_policy_url)
                                                <a href="{{ $application->privacy_policy_url }}" target="_blank">Privacy
                                                    Policy</a>
                                            @else
                                                <span>No URL</span>
                                            @endif
                                        </td>
                                        <td>{{ $application->app_version ?? 'N/A' }}</td>
                                        @can('edit-application')
                                            <td>
                                                <a href="{{ route('applications.edit', $application->id) }}"
                                                    class="btn btn-theme">✏️ Edit</a>
                                            </td>
                                        @endcan
                                        @can('fetch-application')
                                            <td>
                                                <a href="{{ route('applications.fetchData', $application->id) }}"
                                                    class="btn btn-info">🔄 Fetch Data</a>
                                            </td>
                                        @endcan
                                        <td>
                                            <a href="{{ route('applications.play-json', $application->id) }}"
                                                class="btn btn-dark">👁️ View JSON</a>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="10" class="text-center text-muted">😴 No applications found. Time to add
                                        your first one!</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $applications->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
