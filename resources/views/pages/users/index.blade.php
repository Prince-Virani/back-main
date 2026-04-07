@extends('layout.default')

@section('title', 'Users')

@section('content')
    <div class="d-flex align-items-center mb-3">
        <div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Users</a></li>
                <li class="breadcrumb-item active">All Users</li>
            </ul>
            <h1 class="page-header mb-0">👤 Manage Users</h1>
        </div>

        <div class="ms-auto">
            @can('create-users')
                <a href="{{ route('users.create') }}" class="btn btn-theme">
                    <i class="fa fa-plus-circle fa-fw me-1"></i> Add User
                </a>
            @endcan
            <a href="{{ route('dashboard') }}" class="btn btn-secondary ms-2">
                <i class="fa fa-home fa-fw me-1"></i> Go to Dashboard
            </a>
        </div>
    </div>

    <div class="card">
        <div class="tab-content p-4">
            <div class="tab-pane fade show active">
                <div class="table-responsive">
                    <table class="table table-hover text-nowrap align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr id="row-{{ $user->id }}">
                                    <td>{{ $user->id }}</td>
                                    <td class="category-name">{{ $user->name }}</td>
                                    <td class="category-name">{{ $user->email }}</td>
                                    <td class="category-name">{{ $user->roles->pluck('name')->join(', ') }}</td>
                                    <td>
                                        @can('edit-users')
                                            <a class="btn  btn-theme" href="{{ route('users.edit', $user->id) }}">
                                                ✏️ Edit
                                            </a>
                                        @endcan
                                        @can('delete-users')
                                            <button onclick="deleteUser({{ $user->id }})" class="btn btn-danger">🗑️
                                                Delete</button>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">
                                        <div class="alert alert-warning mb-0">
                                            😔 No user found. Hit that "Add User" button!
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($users->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $users->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script src="/assets/js/user.js?ts=<?= time() ?>"></script>
@endsection
