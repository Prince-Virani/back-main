@extends('layout.default')

@section('title', 'Verticals')

@push('styles')
<style>
    .compact .card-header,.compact .card-footer{padding:.25rem .5rem!important}
    .compact .breadcrumb{margin-bottom:.25rem!important;font-size:.8rem}
    .compact h6{margin:0;font-size:.9rem}
    .table-ultra{border-collapse:collapse}
    .table-ultra td,.table-ultra th{padding:1px 4px!important;font-size:.72rem!important;line-height:1.05!important;vertical-align:middle!important}
    .table-ultra thead th{font-weight:600;background:#f8f9fa}
    .btn-xxs{padding:0!important;width:22px;height:22px;display:inline-flex;align-items:center;justify-content:center;line-height:1;border-radius:.35rem;font-size:.75rem}
</style>
@endpush

@section('content')
<div class="d-flex align-items-center mb-2">
    <div>
        <ul class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="#">Verticals</a></li>
            <li class="breadcrumb-item active">All Verticals</li>
        </ul>
        <h6 class="mb-0">📁 Manage Verticals</h6>
    </div>
    <div class="ms-auto d-flex align-items-center gap-1">
        @can('add-manage-verticals')
            <a href="{{ route('verticals.create') }}" class="btn btn-xxs btn-theme"><i class="fa fa-plus"></i></a>
        @endcan
        <a href="{{ route('dashboard') }}" class="btn btn-xxs btn-secondary"><i class="fa fa-home"></i></a>
    </div>
</div>

<div class="card shadow-sm border-0 compact">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-ultra mb-0">
                <thead>
                    <tr>
                        <th>Vertical Name</th>
                        @canany(['edit-manage-verticals','delete-manage-verticals'])
                            <th style="width:120px;">Actions</th>
                        @endcanany
                    </tr>
                </thead>
                <tbody>
                    @forelse($verticals as $vertical)
                        <tr id="row-{{ $vertical->id }}">
                            <td id="name-{{ $vertical->id }}" class="fw-semibold">{{ $vertical->name }}</td>
                            @canany(['edit-manage-verticals','delete-manage-verticals'])
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-xxs btn-outline-primary"
                                                id="edit-btn-{{ $vertical->id }}"
                                                onclick="editvertical({{ $vertical->id }})">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button class="btn btn-xxs btn-success"
                                                id="update-btn-{{ $vertical->id }}"
                                                onclick="updatevertical({{ $vertical->id }})"
                                                style="display:none;">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        <button class="btn btn-xxs btn-secondary"
                                                id="cancel-btn-{{ $vertical->id }}"
                                                onclick="cancelEdit({{ $vertical->id }})"
                                                style="display:none;">
                                            <i class="fa fa-times"></i>
                                        </button>
                                        <button class="btn btn-xxs btn-outline-danger"
                                                onclick="deletevertical({{ $vertical->id }})">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            @endcanany
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center text-muted py-2">
                                <i class="fa fa-inbox me-1"></i> No verticals found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($verticals->hasPages())
        <div class="card-footer bg-transparent d-flex justify-content-center">
            {{ $verticals->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

<script src="/assets/js/verticals.js?ts=<?= time() ?>"></script>
@endsection
