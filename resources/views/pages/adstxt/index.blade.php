@extends('layout.default')

@section('title', 'Ads txt Management')

@push('styles')
<style>
    .table-responsive { overflow-x: auto; }
    .table thead th { position: sticky; top: 0; z-index: 2; background: var(--bs-body-bg); }
    .content-cell { max-width: 520px; }
</style>
@endpush

@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <i class="fa fa-ad fa-lg text-secondary"></i>
                <h5 class="mb-0">Ads txt Management</h5>
            </div>
            @can('add-ads-management')
                <a href="{{ route('adstxt.create') }}" class="btn btn-theme">
                    <i class="fa fa-plus-circle me-1"></i> Create New Ads txt
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
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 220px;">Website</th>
                            <th style="min-width: 340px;">Content</th>
                            <th style="width: 120px;">Status</th>
                            <th style="width: 220px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($adstxt as $adtxt)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $adtxt->website->website_name }}</div>
                                </td>
                                <td class="content-cell">
                                    <div class="text-truncate" style="max-width: 520px;" title="{{ $adtxt->content }}">
                                        <code class="text-break">{{ $adtxt->content }}</code>
                                    </div>
                                </td>
                                <td>
                                    @php $statusLabel = $adtxt->is_active ? 'Inactive' : 'Active'; @endphp
                                    <span class="badge {{ $statusLabel === 'Active' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @can('edit-ads-management')
                                            <a href="{{ route('adstxt.edit', $adtxt->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fa fa-edit me-1"></i> Edit
                                            </a>
                                        @endcan
                                        @can('delete-ads-management')
                                            <form action="{{ route('adstxt.destroy', $adtxt->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="status_flag" value="{{ $adtxt->status_flag == 0 ? 1 : 0 }}">
                                                <button type="submit"
                                                    class="btn btn-sm {{ $adtxt->status_flag == 0 ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                                    <i class="fa {{ $adtxt->status_flag == 0 ? 'fa-ban' : 'fa-check' }} me-1"></i>
                                                    {{ $adtxt->status_flag == 0 ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        @if ($adstxt->isEmpty())
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

        <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
            <small class="text-muted">
                Showing {{ $adstxt->firstItem() ?? 0 }}–{{ $adstxt->lastItem() ?? 0 }} of {{ $adstxt->total() ?? 0 }}
            </small>
            <div>
                {{ $adstxt->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
