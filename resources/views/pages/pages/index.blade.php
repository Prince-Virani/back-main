@extends('layout.default')

@section('title', 'Pages')

@section('content')
    <style>
        :root {
            --pages-table-height: 520px;
            --control-h: 42px;
            --radius: .75rem;
        }

        .card-pages {
            display: flex;
            flex-direction: column;
            min-height: 0;
            border-radius: var(--radius);
            overflow: hidden;
        }

        .card-pages .nav-tabs {
            flex-wrap: nowrap;
            overflow-x: auto;
            scrollbar-width: thin;
        }

        .card-pages .nav-tabs .nav-item {
            white-space: nowrap;
        }

        .card-pages .tab-content {
            overflow: visible;
        }

        .card-pages .tab-pane {
            overflow: visible;
        }

        .toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: .75rem;
            align-items: center;
        }

        .toolbar .btn,
        .toolbar .form-select,
        .toolbar .form-control {
            height: var(--control-h);
        }

        .toolbar .search-wrap {
            flex: 1 1 260px;
            position: relative;
        }

        .toolbar .search-wrap .input-group-text {
            background: transparent;
            border: 0;
            position: absolute;
            inset: 0 auto 0 0;
            width: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }

        .toolbar .search-wrap input {
            padding-left: 36px;
        }

        .table-wrap {
            position: relative;
            border: 1px solid rgba(0, 0, 0, .08);
            border-radius: var(--radius);
            overflow: hidden;
        }

        .table-scroll {
            max-height: var(--pages-table-height);
            overflow-y: auto;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table {
            margin-bottom: 0;
            table-layout: auto;
        }

        .table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .table td,
        .table th {
            vertical-align: middle;
        }

        .card-footer.sticky-footer {
            position: sticky;
            bottom: 0;
            z-index: 3;
            border-top: 1px solid rgba(0, 0, 0, .08);
        }

        .header-actions .btn {
            height: var(--control-h);
        }

        @media (max-width: 991.98px) {
            :root {
                --pages-table-height: 60vh;
            }
        }

        @media (max-width: 767.98px) {
            .page-header {
                font-size: 1.25rem;
            }

            .breadcrumb {
                display: none;
            }

            .toolbar {
                gap: .5rem;
            }
        }

        #entryCountText {
            min-height: 1.5rem;
        }
    </style>

    <div class="d-flex align-items-center mb-3">
        <div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Pages</a></li>
                <li class="breadcrumb-item active">All Pages</li>
            </ul>
            <h1 class="page-header mb-0">📄 Manage Pages</h1>
        </div>
        <div class="ms-auto d-flex gap-2">
            @can('add-pages')
                <a href="{{ route('pages.create') }}" class="btn btn-theme">
                    <i class="fa fa-plus-circle fa-fw me-1"></i> Add Page
                </a>
            @endcan
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                <i class="fa fa-home fa-fw me-1"></i> Dashboard
            </a>
        </div>
    </div>

    <div class="card card-pages shadow-sm">
        <ul class="nav nav-tabs nav-tabs-v2 px-4">
            <li class="nav-item me-3"><a href="#allTab" class="nav-link active px-2" data-bs-toggle="tab"
                    data-status="2">All</a></li>
            <li class="nav-item me-3"><a href="#allTab" class="nav-link px-2" data-bs-toggle="tab"
                    data-status="0">Active</a></li>
            <li class="nav-item me-3"><a href="#allTab" class="nav-link px-2" data-bs-toggle="tab"
                    data-status="1">Inactive</a></li>
        </ul>

        <div class="tab-content p-4">
            <div class="tab-pane fade show active" id="allTab">
                <div class="toolbar mb-3">
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                            aria-expanded="false">Filter Pages</button>
                        <div class="dropdown-menu p-3" style="min-width: 300px;">
                            <div class="mb-3">
                                <label class="form-label">🌐 Website</label>
                                <select id="websiteFilter" class="form-select">
                                    <option value="0" selected>All Websites</option>
                                    @foreach ($websites as $website)
                                        <option value="{{ $website->id }}">{{ $website->website_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label">📌 Status</label>
                                <select id="statusFilter" class="form-select">
                                    <option value="0">Active</option>
                                    <option value="1">Inactive</option>
                                    <option value="2" selected>All</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="search-wrap">
                        <span class="input-group-text"><i class="fa fa-search opacity-50"></i></span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Search pages...">
                    </div>
                </div>

                <div class="table-wrap">
                    <div class="table-scroll">
                        <table class="table table-hover text-nowrap align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 90px;">ID</th>
                                    <th>Page Name</th>
                                    <th>Page Category</th>
                                    <th style="width: 90px;">Edit</th>
                                    <th style="width: 110px;">Status</th>
                                </tr>
                            </thead>
                            <tbody id="pagesTableBody"></tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <div class="card-footer sticky-footer">
            <div class="d-md-flex align-items-center gap-2">
                <div class="me-md-auto text-md-left text-center mb-2 mb-md-0" id="entryCountText" aria-live="polite"></div>
                <ul class="pagination mb-0 justify-content-center" id="paginationWrapper"></ul>
            </div>
        </div>
    </div>

    <input type="hidden" id="currentPage" value="1">

    <script>
        const indexpagesUrl = "{{ url('pages/data-list') }}";
        const pagesUrl = "{{ url('pages') }}";
		window.userPermissions = @json(auth()->user()->getAllPermissions()->pluck('name'));
    </script>

    <script src="/assets/js/pages.js?ts=<?= time() ?>"></script>
@endsection
