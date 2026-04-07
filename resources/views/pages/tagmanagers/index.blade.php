@extends('layout.default')

@section('title', 'Tag Manager')

@push('styles')
<style>
    .table-responsive { overflow-x: auto; }
    .table thead th { position: sticky; top: 0; z-index: 2; background: var(--bs-body-bg); }
    .table-sm td, .table-sm th { padding: .45rem .6rem !important; vertical-align: middle !important; }
    .content-cell { max-width: 520px; }
    .btn-icon { padding: 2px 8px !important; line-height: 1; }
</style>
@endpush

@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <i class="fa fa-tags fa-lg text-secondary"></i>
                <h5 class="mb-0">Tag Manager</h5>
            </div>
            @can('add-tag-manager')
                <a href="{{ route('tagmanagers.create') }}" class="btn btn-theme">
                    <i class="fa fa-plus-circle me-1"></i> Create New Tag Manager
                </a>
            @endcan
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 rounded-0 mb-0">
                <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
            </div>
        @endif

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width:220px;">Website</th>
                            <th style="min-width:360px;">Content</th>
                            <th style="width:120px;">Status</th>
                            @canany(['edit-tag-manager', 'delete-tag-manager'])
                                <th style="width:220px;">Actions</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tagmanagers as $tagmanager)
                            @php $active = ($tagmanager->status_flag == 0); @endphp
                            <tr>
                                <td class="fw-semibold">{{ $tagmanager->website->website_name }}</td>
                                <td class="content-cell">
                                    <div class="text-truncate" style="max-width:520px;" title="{{ $tagmanager->content }}">
                                        <code class="text-break">{{ Str::limit($tagmanager->content, 120) }}</code>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                @canany(['edit-tag-manager', 'delete-tag-manager'])
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @can('edit-tag-manager')
                                                <a href="{{ route('tagmanagers.edit', $tagmanager->id) }}"
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fa fa-edit me-1"></i> Edit
                                                </a>
                                            @endcan
                                            @can('delete-tag-manager')
                                                <form action="{{ route('tagmanagers.destroy', $tagmanager->id) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="status_flag" value="{{ $active ? 1 : 0 }}">
                                                    <button type="submit"
                                                        class="btn btn-sm {{ $active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                                        <i class="fa {{ $active ? 'fa-ban' : 'fa-check' }} me-1"></i>
                                                        {{ $active ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                @endcanany
                            </tr>
                        @endforeach

                        @if ($tagmanagers->isEmpty())
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fa fa-inbox me-1"></i> No records found.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        @if(method_exists($tagmanagers, 'links'))
            <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Showing {{ $tagmanagers->firstItem() ?? 0 }}–{{ $tagmanagers->lastItem() ?? 0 }}
                    of {{ $tagmanagers->total() ?? ($tagmanagers->count() ?? 0) }}
                </small>
                <div>
                    {{ $tagmanagers->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
