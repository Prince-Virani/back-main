@extends('layout.default')

@section('title', 'Ad Positions')

@push('styles')
<style>
    .table-compact td, 
    .table-compact th {
        padding: 2px 6px !important;
        font-size: 0.8rem;
        line-height: 1.1;
        vertical-align: middle !important;
    }
    .table-compact thead th {
        font-size: 0.8rem;
        font-weight: 600;
    }
    .table-compact .badge {
        padding: 1px 6px;
        font-size: 0.65rem;
    }
    .btn-icon {
        padding: 1px 4px !important;
        font-size: 0.7rem;
        line-height: 1;
    }
</style>
@endpush

@section('content')
<div class="container-fluid mt-3">
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex align-items-center justify-content-between py-2">
            <div class="d-flex align-items-center gap-2">
                <i class="fa fa-th-large fa-lg text-secondary"></i>
                <h6 class="mb-0">Ad Positions</h6>
            </div>
            @can('add-ads-position')
                <a href="{{ route('adpositions.create') }}" class="btn btn-sm btn-theme">
                    <i class="fa fa-plus-circle me-1"></i> Add New Position
                </a>
            @endcan
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 rounded-0 mb-0 py-2 px-3">
                <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
            </div>
        @endif

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-compact table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Position Name</th>
                            <th>Status</th>
                            @canany(['edit-ads-position','delete-ads-position'])
                                <th>Actions</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($adPositions as $adPosition)
                            @php $isActive = ($adPosition->status_flag == 0); @endphp
                            <tr>
                                <td class="fw-semibold">{{ $adPosition->position_name }}</td>
                                <td>
                                    <span class="badge {{ $isActive ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $isActive ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                @canany(['edit-ads-position','delete-ads-position'])
                                    <td>
                                        <div class="d-flex gap-1">
                                            @can('edit-ads-position')
                                                <a href="{{ route('adpositions.edit', $adPosition->id) }}"
                                                   class="btn btn-outline-primary btn-icon">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            @endcan
                                            @can('delete-ads-position')
                                                <form action="{{ route('adpositions.destroy', $adPosition->id) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="status_flag" value="{{ $isActive ? 1 : 0 }}">
                                                    <button type="submit"
                                                        class="btn btn-icon {{ $isActive ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                                        <i class="fa {{ $isActive ? 'fa-ban' : 'fa-check' }}"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                @endcanany
                            </tr>
                        @endforeach
                        @if ($adPositions->isEmpty())
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">
                                    <i class="fa fa-inbox me-1"></i> No positions found.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
