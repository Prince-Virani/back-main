@extends('layout.default')

@section('content')
    <style>
        :root {
            --primary-color: #6366f1;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --dark-color: #1f2937;
            --light-color: #ffffff;
            --border-radius: 8px;
            --box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            --transition: all 0.2s ease;
        }

        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border-bottom: 1px solid #e5e7eb;
            padding: 1.25rem;
        }

        .card-header strong {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark-color);
        }

        .badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .bg-info {
            background: linear-gradient(135deg, var(--primary-color), #8b5cf6) !important;
            color: white !important;
        }

        .card-body {
            padding: 1.25rem;
        }

        .btn-theme {
            background: linear-gradient(135deg, var(--primary-color), #8b5cf6);
            border: none;
            color: white;
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            font-weight: 500;
            transition: var(--transition);
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .btn-theme:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(99, 102, 241, 0.3);
            color: white;
        }

        .text-muted {
            background: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 6px;
            padding: 0.75rem;
            margin-bottom: 1rem;
            color: #92400e !important;
        }

        .modal-content {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), #8b5cf6);
            color: white;
            border-bottom: none;
            padding: 1.25rem;
        }

        .modal-title {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .btn-close {
            filter: brightness(0) invert(1);
            opacity: 0.8;
        }

        .modal-body {
            padding: 1.5rem;
            max-height: 70vh;
            overflow-y: auto;
        }

        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e5e7eb;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.4rem;
        }

        .form-control,
        .form-select {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 0.6rem;
            transition: var(--transition);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
        }

        .table-responsive {
            border-radius: 6px;
            overflow: hidden;
            box-shadow: var(--box-shadow);
        }

        .table thead th {
            background: linear-gradient(135deg, var(--primary-color), #8b5cf6);
            color: white;
            border: none;
            padding: 0.75rem;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .table tbody td {
            padding: 0.75rem;
            border-color: #e5e7eb;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #f8fafc;
        }

        .form-switch .form-check-input {
            width: 2.5rem;
            height: 1.25rem;
            background-color: #d1d5db;
            border: none;
            border-radius: 1rem;
        }

        .form-switch .form-check-input:checked {
            background-color: var(--success-color);
        }

        .spinner-border {
            width: 2rem;
            height: 2rem;
            border-width: 0.2em;
        }

        @media (max-width: 768px) {

            .card-header,
            .card-body {
                padding: 1rem;
            }

            .btn-theme {
                width: 100%;
                margin-right: 0;
                margin-bottom: 0.5rem;
            }

            .modal-dialog {
                margin: 0.5rem;
            }

            .modal-dialog-xl {
                max-width: calc(100vw - 1rem);
            }

            .modal-body {
                padding: 1rem;
                max-height: 60vh;
                overflow: visible;
            }

            .table-responsive {
                overflow-x: scroll;
                -webkit-overflow-scrolling: touch;
                border-radius: 6px;
                width: 100%;
            }

            .table {
                min-width: 1200px;
                width: auto;
            }

            .table thead th,
            .table tbody td {
                padding: 0.5rem;
                font-size: 0.8rem;
                white-space: nowrap;
            }

            .card-header strong {
                font-size: 1rem;
            }

            .badge {
                font-size: 0.7rem;
                padding: 0.3rem 0.6rem;
            }
        }

        @media (max-width: 576px) {
            .table-responsive {
                font-size: 0.75rem;
                overflow-x: scroll;
                -webkit-overflow-scrolling: touch;
            }

            .table {
                min-width: 1500px;
            }

            .table thead th,
            .table tbody td {
                padding: 0.4rem;
                font-size: 0.7rem;
                white-space: nowrap;
            }

            .modal-body {
                max-height: 50vh;
                overflow: visible;
                padding: 0.5rem;
            }

            .btn-theme {
                padding: 0.5rem 1rem;
                font-size: 0.85rem;
            }

            .modal-header {
                padding: 1rem;
            }

            .modal-title {
                font-size: 1rem;
            }
        }

        .modal-body::-webkit-scrollbar {
            width: 4px;
        }

        .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .modal-body::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 2px;
        }
    </style>

    <div class="container mt-4">
        <h2 class="mb-4">Ad Units</h2>
        <div class="row">
            @foreach ($websites as $index => $website)
                @if ($index % 2 === 0 && $index > 0)
        </div>
        <div class="row">
            @endif
            @php
                $LOCK_POSITIONS = ['header', 'footer', 'interstitial'];
                $taken = $website->adUnits
                    ->whereIn('ad_unit_type', $LOCK_POSITIONS)
                    ->pluck('ad_unit_type')
                    ->map(fn($v) => trim((string) $v))
                    ->values()
                    ->all();
            @endphp
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong>{{ $website->website_name }}</strong>
                        <span class="badge bg-info text-dark">{{ $website->ad_units_count }} Ad Units</span>
                    </div>
                    <div class="card-body">
                        @if ($website->ad_units_count > 0)
                            <button class="btn btn-theme" data-bs-toggle="modal"
                                data-bs-target="#viewModal{{ $website->id }}">View Ad Unit Details</button>
                        @else
                            <p class="text-muted">No ad units available.</p>
                        @endif
                        @can('add-ad-unit')
                            <button class="btn btn-theme" data-bs-toggle="modal"
                                data-bs-target="#createModal{{ $website->id }}">Create Ad Unit</button>
                        @endcan
                    </div>
                </div>

                <div class="modal fade" id="viewModal{{ $website->id }}" tabindex="-1">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Ad Units for {{ $website->website_name }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div id="adunit-loading-{{ $website->id }}" class="text-center d-none mb-3">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                                <div id="adunit-details-{{ $website->id }}">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Name</th>
                                                    <th>Code</th>
                                                    <th>Ad Position</th>
                                                    <th>In Page After Words</th>
                                                    <th>Sizes</th>
                                                    <th>Status</th>
                                                    <th>Is Lazy</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($website->adUnits as $unit)
                                                    <tr>
                                                        <td>{{ $unit->adunit_id }}</td>
                                                        <td>{{ $unit->adunit_name }}</td>
                                                        <td>{{ $unit->ad_unit_code }}</td>
                                                        <td>{{ $unit->ad_unit_type }}</td>
                                                        <td>{{ $unit->in_page_position }}</td>
                                                        <td>
                                                            @php
                                                                $parsedSizes = [];
                                                                $rawSizes = $unit->ad_unit_size;
                                                                $parts = explode(',', $rawSizes);
                                                                $current = '';
                                                                foreach ($parts as $part) {
                                                                    $part = trim($part);
                                                                    if ($part === '"fluid"' || $part === 'fluid') {
                                                                        $parsedSizes[] = 'fluid';
                                                                        continue;
                                                                    }
                                                                    if (str_starts_with($part, '[')) {
                                                                        $current = $part;
                                                                    } elseif (str_ends_with($part, ']')) {
                                                                        $current .= ',' . $part;
                                                                        $decoded = json_decode($current, true);
                                                                        if (
                                                                            is_array($decoded) &&
                                                                            count($decoded) === 2
                                                                        ) {
                                                                            $parsedSizes[] = $decoded;
                                                                        }
                                                                        $current = '';
                                                                    } elseif (!empty($current)) {
                                                                        $current .= ',' . $part;
                                                                    }
                                                                }
                                                            @endphp
                                                            @if (count($parsedSizes))
                                                                @foreach ($parsedSizes as $size)
                                                                    @if (is_array($size))
                                                                        {{ $size[0] }}x{{ $size[1] }}<br>
                                                                    @elseif($size === 'fluid')
                                                                        <span class="badge bg-secondary">fluid</span><br>
                                                                    @endif
                                                                @endforeach
                                                            @else
                                                                <span class="text-danger">Invalid sizes</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @can('edit-ad-unit-status')
                                                                <form class="toggle-status-form"
                                                                    data-url="{{ route('adunits.toggleStatus', $unit->adunit_id) }}"
                                                                    method="POST">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <input type="hidden" name="website_id"
                                                                        value="{{ $website->id }}">
                                                                    <div class="form-check form-switch">
                                                                        <input type="checkbox"
                                                                            class="form-check-input toggle-status-checkbox"
                                                                            name="status_flag"
                                                                            {{ $unit->status_flag ? 'checked' : '' }}>
                                                                    </div>
                                                                </form>
                                                            @endcan
                                                        </td>
                                                        <td>
                                                            @can('edit-ad-unit-lazy')
                                                                <form class="toggle-lazy-form"
                                                                    data-url="{{ route('adunits.toggleLazy', $unit->adunit_id) }}"
                                                                    method="POST">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <input type="hidden" name="website_id"
                                                                        value="{{ $website->id }}">
                                                                    <div class="form-check form-switch">
                                                                        <input type="checkbox"
                                                                            class="form-check-input toggle-lazy-checkbox"
                                                                            name="is_lazy"
                                                                            {{ $unit->is_lazy ? 'checked' : '' }}>
                                                                    </div>
                                                                </form>
                                                            @endcan
                                                        </td>
                                                        <td>
                                                            @can('delete-ad-unit')
                                                                <button type="button"
                                                                    class="btn btn-sm btn-danger delete-adunit-btn"
                                                                    data-adunit-id="{{ $unit->adunit_id }}"
                                                                    data-website-id="{{ $website->id }}"
                                                                    data-adunit-name="{{ $unit->adunit_name }}">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            @endcan
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="createModal{{ $website->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form onsubmit="createAdUnit(event, {{ $website->id }})">
                                <div class="modal-header">
                                    <h5 class="modal-title">Create Ad Unit for {{ $website->website_name }}</h5>
                                    <button type="button" class="btn-close" aria-label="Close"
                                        data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Select Position</label>
                                        <select class="form-select" name="position" id="position-{{ $website->id }}"
                                            required>
                                            <option value="">-- Select Position --</option>
                                            @foreach ($adPositions as $pos)
                                                @php
                                                    $posName = trim($pos->position_name);
                                                    $isLocked =
                                                        in_array($posName, $LOCK_POSITIONS, true) &&
                                                        in_array($posName, $taken, true);
                                                @endphp
                                                <option value="{{ $posName }}" {{ $isLocked ? 'disabled' : '' }}>
                                                    {{ $posName }} {{ $isLocked ? '(already in use)' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Ad Unit Name</label>
                                        <input type="text" class="form-control" id="adunit-name-{{ $website->id }}"
                                            required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Ad Unit Code</label>
                                        <input type="text" class="form-control" id="adunit-code-{{ $website->id }}"
                                            required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">In-Page Position (after X words)</label>
                                        <input type="text" class="form-control" id="adunit-in-page-{{ $website->id }}"
                                            value="0">
                                    </div>
                                    <div id="create-result-{{ $website->id }}" class="small"></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-theme">Create</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>


            </div>
            @endforeach
        </div>
    </div>
    <script src="/assets/js/adunit.js?ts=<?= time() ?>"></script>
@endsection
