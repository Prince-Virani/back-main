@extends('layout.default')
@section('title', 'Firestore App Settings')
@section('content')
    <div class="d-flex align-items-center mb-3">
        <div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Firestore</a></li>
                <li class="breadcrumb-item active">All Settings</li>
            </ul>
            <h1 class="page-header mb-0">Manage Firestore App Settings</h1>
        </div>
        @can('add-firestore-app-setting')
            <div class="ms-auto">
                <a href="{{ route('firestore-app-settings.create') }}" class="btn btn-theme"><i
                        class="fa fa-plus-circle me-1"></i>Add Setting</a>
            </div>
        @endcan
    </div>

    <div class="card border-0">
        <div class="card-body">
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}<button
                        type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button
                        type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif
        </div>

        <div class="table-responsive p-4">
            <table class="table table-hover text-nowrap align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Application Package</th>
                        <th>FIREBASE_PROJECT_ID</th>
                        <th>Collection</th>
                        <th>Document</th>
                        @can('change-firestore-app-setting')
                            <th>Status</th>
                        @endcan
                        @can('edit-firestore-app-setting')
                            <th>Edit</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @forelse($settings as $key=>$s)
                        <tr>
                            <td>{{ $settings->firstItem() + $key }}</td>
                            <td>{{ $s->application_package }}</td>
                            <td>{{ $s->firebase_project_id }}</td>
                            <td>{{ $s->collection_name }}</td>
                            <td>{{ $s->document_name }}</td>
                            @can('change-firestore-app-setting')
                                <td>
                                    <form action="{{ route('firestore-app-settings.toggle', $s->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        <input type="hidden" name="status_flag" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="status_flag" value="1"
                                                {{ $s->status_flag ? 'checked' : '' }} onchange="this.form.submit()">
                                        </div>
                                    </form>
                                </td>
                            @endcan
                            @can('edit-firestore-app-setting')
                                <td><a href="{{ route('firestore-app-settings.edit', $s->id) }}"
                                        class="btn btn-theme btn-sm">Edit</a></td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No settings found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="d-flex justify-content-center mt-3">{{ $settings->links() }}</div>
        </div>
    </div>
@endsection
