@extends('layout.default')

@section('title', 'Roles')

@section('content')
<link rel="stylesheet" href="{{ asset('css/roles.css') }}">

<div class="d-flex align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item">
                    <a href="#" class="text-decoration-none text-primary fw-medium">
                        <i class="fas fa-shield-alt me-1"></i>Roles
                    </a>
                </li>
                <li class="breadcrumb-item active text-muted">Show Role</li>
            </ol>
        </nav>
        <h1 class="page-header mb-0 fw-bold text-dark">
            <i class="fas fa-eye text-purple me-2"></i>Role Details
        </h1>
        <p class="text-muted mb-0">View role name and assigned permissions</p>
    </div>
    <div class="ms-auto">
        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<div class="enhanced-role-card">
    <div class="card-body">
        <div class="mb-4">
            <h5 class="fw-semibold text-dark mb-2">
                <i class="fas fa-user-tag text-primary me-2"></i>Role Name
            </h5>
            <p class="fs-5 mb-0">{{ $role->name }}</p>
        </div>

        <div>
            <h5 class="fw-semibold text-dark mb-2">
                <i class="fas fa-key text-warning me-2"></i>Permissions
            </h5>
            @if (!empty($rolePermissions) && count($rolePermissions))
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($rolePermissions as $v)
                        <span class="badge bg-light text-dark border shadow-sm px-3 py-2">
                            <i class="fas fa-check-circle text-success me-1"></i>{{ $v->name }}
                        </span>
                    @endforeach
                </div>
            @else
                <p class="text-muted fst-italic mb-0">No permissions assigned to this role.</p>
            @endif
        </div>
    </div>
</div>
@endsection
