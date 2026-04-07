@extends('layout.default')

@section('title', 'Links')

@section('content')
<div class="d-flex align-items-center mb-3">
    <div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Links</a></li>
            <li class="breadcrumb-item active">All Links</li>
        </ul>
        <h1 class="page-header mb-0">🔗 Links Manager</h1>
    </div>
    <div class="ms-auto d-flex align-items-center gap-2">
        @can('add-link-manager')
        <button type="button" class="btn btn-theme" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="fa fa-plus-circle fa-fw me-1"></i> Add Link
        </button>
        @endcan
        <a href="{{ route('dashboard') }}" class="btn btn-secondary ms-2">
            <i class="fa fa-home fa-fw me-1"></i> Go to Dashboard
        </a>
        <div id="ajaxAlertsContainer"></div>
    </div>
</div>

@if (session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if (session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card">
    <div class="p-3">
        <form method="GET" action="{{ route('links.index') }}" class="row g-2 mb-3">
            <div class="col-12 col-md-3">
                <input name="search" value="{{ request('search') }}" type="search" class="form-control"
                    placeholder="Search by URL...">
            </div>
            <div class="col-6 col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="1" @selected(request('status')==='1' )>Active</option>
                    <option value="0" @selected(request('status')==='0' )>Inactive</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select name="package_name" class="form-select">
                    <option value="">All Packages</option>
                    @foreach ($packages as $p)
                    <option value="{{ $p->application_package }}" @selected(request('package_name')===$p->application_package)>
                        {{ $p->application_package }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3 d-grid d-md-flex">
                <button class="btn btn-primary me-md-2" type="submit">Filter</button>
                <a href="{{ route('links.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover text-nowrap align-middle">
                <thead>
                    <tr>
                        <th style="width:60px">#</th>
                        <th>URL</th>
                        <th style="width:180px">App Package</th>
                        <th style="width:120px">Counter</th>
                        <th style="width:120px">Status</th>
                        @canany(['edit-link-manager', 'delete-link-manager', 'change-status-link-manager'])
                        <th style="width:260px">Actions</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @forelse($links as $link)
                    <tr data-row-id="{{ $link->id }}">
                        <td><strong>{{ $links->firstItem() + $loop->index }}</strong></td>
                        <td class="text-break">
                            <a href="{{ $link->url }}" target="_blank" rel="noopener">{{ $link->url }}</a>
                        </td>
                        <td>{{ $link->package_name }}</td>
                        <td>{{ $link->counter }}</td>
                        <td class="status-cell">
                            @if ($link->status_flag)
                            <span class="badge bg-success">Active</span>
                            @else
                            <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        @canany(['edit-link-manager', 'delete-link-manager', 'change-status-link-manager'])
                        <td class="text-nowrap">
                            <button type="button" class="btn btn-sm btn-outline-primary me-1" data-edit
                                data-id="{{ $link->id }}" data-url="{{ $link->url }}"
                                data-counter="{{ $link->counter }}" data-status="{{ $link->status_flag }}"
                                data-package="{{ $link->package_name }}"
                                data-action="{{ route('links.update', $link) }}">
                                <i class="fa fa-pencil me-1"></i> Edit
                            </button>

                            <form action="{{ route('links.toggle', $link) }}" method="POST" class="d-inline"
                                data-ajax="toggle">
                                @csrf
                                @method('PATCH')
                                @if ($link->status_flag)
                                <button type="button" class="btn btn-sm btn-outline-warning me-1"
                                    data-role="toggle-btn">
                                    <i class="fa fa-pause me-1"></i> Pause
                                </button>
                                @else
                                <button type="button" class="btn btn-sm btn-outline-success me-1"
                                    data-role="toggle-btn">
                                    <i class="fa fa-play me-1"></i> Activate
                                </button>
                                @endif
                            </form>

                            <form action="{{ route('links.destroy', $link) }}" method="POST" class="d-inline"
                                data-ajax="delete">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-sm btn-outline-danger" data-role="delete-btn">
                                    <i class="fa fa-trash me-1"></i> Delete
                                </button>
                            </form>
                        </td>
                        @endcan
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No links found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $links->links() }}
        </div>
    </div>
</div>

<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('links.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add Link</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if ($errors->create->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->create->all() as $e)
                        <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <div class="vstack gap-3">
                    <div>
                        <label class="form-label">URL <span class="text-danger">*</span></label>
                        <input type="url" name="url" value="{{ old('url') }}" class="form-control"
                            required>
                    </div>
                    <div>
                        <label class="form-label">App Package <span class="text-danger">*</span></label>
                        <select name="package_name" class="form-select" required>
                            <option value="">Select app package</option>
                            @foreach ($packages as $p)
                            <option value="{{ $p->application_package }}" @selected(old('package_name')===$p->application_package)>
                                {{ $p->application_package }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Counter</label>
                            <input type="number" name="counter" value="{{ old('counter', 0) }}"
                                class="form-control" min="0" step="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status_flag" class="form-select">
                                <option value="1" @selected(old('status_flag', 1)==1)>Active</option>
                                <option value="0" @selected(old('status_flag', 1)==0)>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-theme"><span class="btn-text">Save</span></button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="editForm" action="#" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Edit Link</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if ($errors->edit->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->edit->all() as $e)
                        <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <div class="vstack gap-3">
                    <div>
                        <label class="form-label">URL <span class="text-danger">*</span></label>
                        <input type="url" name="url" id="e_url" value="{{ old('url') }}"
                            class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label">App Package <span class="text-danger">*</span></label>
                        <select name="package_name" id="e_package" class="form-select" required>
                            <option value="">Select app package</option>
                            @foreach ($packages as $p)
                            <option value="{{ $p->application_package }}" @selected(old('package_name')===$p->application_package)>
                                {{ $p->application_package }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Counter</label>
                            <input type="number" name="counter" id="e_counter" value="{{ old('counter') }}"
                                class="form-control" min="0" step="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status_flag" id="e_status" class="form-select">
                                <option value="1" @selected(old('status_flag')==1)>Active</option>
                                <option value="0" @selected(old('status_flag')==0)>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-theme"><span class="btn-text">Update</span></button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
</script>
<script data-cfasync="false">
    if (!window.__LINKS_PAGE_INIT__) {
        window.__LINKS_PAGE_INIT__ = true;

        document.addEventListener('DOMContentLoaded', () => {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const alertBox = document.getElementById('ajaxAlertsContainer');

            document.addEventListener('submit', (e) => {
                const form = e.target;
                if (form.matches('[data-ajax]')) return;
                const btn = form.querySelector('button[type="submit"]');
                if (btn && !btn.dataset.loading) {
                    btn.dataset.loading = '1';
                    btn.disabled = true;
                    const txt = btn.querySelector('.btn-text');
                    if (txt) txt.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span>Working…';
                    else btn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span>Working…';
                }
            }, true);

            document.addEventListener('click', async (e) => {
                const toggleBtn = e.target.closest(
                    'form[data-ajax="toggle"] [data-role="toggle-btn"]');
                const deleteBtn = e.target.closest(
                    'form[data-ajax="delete"] [data-role="delete-btn"]');
                if (!toggleBtn && !deleteBtn) return;

                e.preventDefault();
                e.stopPropagation();

                const btn = toggleBtn || deleteBtn;
                const form = btn.closest('form');
                const row = form.closest('tr[data-row-id]');
                const original = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                try {
                    if (deleteBtn && !confirm('Delete this link?')) {
                        btn.disabled = false;
                        btn.innerHTML = original;
                        return;
                    }

                    const method = toggleBtn ? 'PATCH' : 'DELETE';
                    const res = await fetch(form.action, {
                        method,
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    });

                    if (!res.ok) throw new Error();

                    if (toggleBtn) {
                        const data = await res.json();
                        const active = +data.item.status_flag === 1;
                        const statusCell = row.querySelector('.status-cell');
                        statusCell.innerHTML = active ?
                            '<span class="badge bg-success">Active</span>' :
                            '<span class="badge bg-secondary">Inactive</span>';

                        if (active) {
                            btn.className = 'btn btn-sm btn-outline-warning me-1';
                            btn.innerHTML = '<i class="fa fa-pause me-1"></i> Pause';
                        } else {
                            btn.className = 'btn btn-sm btn-outline-success me-1';
                            btn.innerHTML = '<i class="fa fa-play me-1"></i> Activate';
                        }
                        flash('Status updated.');
                    } else {
                        await res.json();
                        row?.remove();
                        flash('Link deleted.');
                    }
                } catch (_) {
                    flash('Action failed. Please try again.', 'danger');
                    btn.innerHTML = original;
                } finally {
                    btn.disabled = false;
                }
            });

            document.body.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-edit]');
                if (!btn) return;
                const m = new bootstrap.Modal(document.getElementById('editModal'));
                const form = document.getElementById('editForm');
                form.action = btn.dataset.action;
                document.getElementById('e_url').value = btn.dataset.url || '';
                document.getElementById('e_counter').value = btn.dataset.counter || 0;
                document.getElementById('e_status').value = btn.dataset.status || 1;

                const pkgSelect = document.getElementById('e_package');
                const wanted = btn.dataset.package || '';
                if (wanted) {
                    let opt = Array.from(pkgSelect.options).find(o => o.value === wanted);
                    if (!opt) {
                        opt = new Option(wanted + ' (inactive)', wanted, true, true);
                        pkgSelect.add(opt, 1);
                    } else {
                        pkgSelect.value = wanted;
                    }
                } else {
                    pkgSelect.value = '';
                }

                m.show();
            });

            @if(session('openModal') === 'create')
            new bootstrap.Modal(document.getElementById('createModal')).show();
            @endif
            @if(session('openModal') === 'edit' && session('edit_id'))
            const em = new bootstrap.Modal(document.getElementById('editModal'));
            document.getElementById('editForm').action = @json(route('links.update', session('edit_id')));
            em.show();
            @endif

            function flash(message, type = 'success') {
                if (!alertBox) return;
                alertBox.innerHTML =
                    `<div class="alert alert-${type} alert-dismissible fade show ms-2" role="alert">
           ${message}
           <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
         </div>`;
            }
        });
    }
</script>
@endpush