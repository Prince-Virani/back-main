@extends('layout.default')

@section('title', 'Roles')

@section('content')
    <div class="d-flex align-items-center mb-3">
        <div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Roles</a></li>
                <li class="breadcrumb-item active">All Roles</li>
            </ul>
            <h1 class="page-header mb-0">📁 Manage Role</h1>
        </div>

        <div class="ms-auto">
            @can('create-roles')
                <a href="{{ route('roles.create') }}" class="btn btn-theme">
                    <i class="fa fa-plus-circle fa-fw me-1"></i> Add Role
                </a>
            @endcan
            <a href="{{ route('dashboard') }}" class="btn btn-secondary ms-2">
                <i class="fa fa-home fa-fw me-1"></i> Go to Dashboard
            </a>
        </div>
    </div>

    <div class="card">
        <div class="tab-content p-4">
            <div class="tab-pane fade show active" id="allTab">
                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover text-nowrap align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($roles as $key => $role)
                                <tr>
                                    <td>{{ ++$i }}</td>
                                    <td>{{ $role->name }}</td>
                                    <td>
                                        <a class="btn btn-info" href="{{ route('roles.show', $role->id) }}">Show</a>
                                        @can('edit-roles')
                                            <a class="btn btn-primary" href="{{ route('roles.edit', $role->id) }}">Edit</a>
                                        @endcan
                                        @can('delete-roles')
                                        @if (strtolower($role->name) !== 'admin')
                                            <form action="{{ route('roles.destroy', $role->id) }}" method="POST"
                                                style="display:inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Delete</button>
                                            </form>
                                        @endif
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">
                                        <div class="alert alert-warning mb-0">
                                            😔 No Roles found. Hit that "Add Role" button!
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                @if ($roles->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $roles->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
