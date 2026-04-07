@extends('layout.default')

@section('title', 'Roles')

@push('css')

<link href="/assets/css/roles.css" rel="stylesheet">
@endpush

@section('content')

<div class="d-flex align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item">
                    <a href="#" class="text-decoration-none text-primary fw-medium">
                        <i class="fas fa-shield-alt me-1"></i>Roles
                    </a>
                </li>
                <li class="breadcrumb-item active text-muted">Edit Role</li>
            </ol>
        </nav>
        <h1 class="page-header mb-0 fw-bold text-dark">Edit Role</h1>
        <p class="text-muted mb-0">Manage role permissions and access controls</p>
    </div>
    <div class="ms-auto">
        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Roles
        </a>
    </div>
</div>

<div class="enhanced-role-card">
    @if ($errors->any())
    <div class="alert alert-danger border-0 shadow-sm mb-4">
        <div class="d-flex align-items-start">
            <i class="fas fa-exclamation-triangle text-danger me-3 mt-1"></i>
            <div>
                <strong class="fw-semibold">Validation Error</strong>
                <p class="mb-2 mt-1">Please fix the following issues:</p>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                    <li class="text-sm">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <form action="{{ route('roles.update', $role->id) }}" method="POST" id="roleForm">
        @csrf
        @method('PATCH')

        <div class="row g-4">
            <div class="col-12 col-lg-4">
                <div class="info-card h-100">
                    <div class="card-header">
                        <h5 class="mb-0 fw-semibold text-dark">
                            <i class="fas fa-user-tag me-2 text-primary"></i>Role Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark mb-2">Role Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-tag text-muted"></i>
                                </span>
                                <input type="text" name="name" value="{{ old('name', $role->name) }}" class="form-control border-start-0 ps-0" placeholder="e.g., Admin, Editor, Viewer" id="roleName" disabled>
                            </div>
                            <small class="text-muted">Choose a descriptive name for this role</small>
                        </div>
                        <div class="stats-section mb-4">
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="stat-card">
                                        <div class="stat-number" id="selectedCount">0</div>
                                        <div class="stat-label">Selected</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-card">
                                        <div class="stat-number">{{ count($permission) }}</div>
                                        <div class="stat-label">Total</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="selected-section">
                            <div class="section-header mb-3">
                                <h6 class="fw-semibold text-dark mb-0">Selected Permissions</h6>
                                <small class="text-muted">Quick preview of assigned permissions</small>
                            </div>
                            <div id="selectedChips" class="chips-container"></div>
                        </div>
                        <div class="action-section mt-4">
                            <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            @php
            $grouped = [];
            foreach ($permission as $perm) {
                $name = $perm->name;
                $norm = str_replace(['-',':','.',' '],' ',$name);
                $key = preg_match('/^([A-Za-z0-9_]+)/', $norm, $m) ? strtolower($m[1]) : 'misc';
                $grouped[$key][] = $name;
            }
            ksort($grouped);
            $selected = collect($rolePermissions ?? [])->toArray();
            @endphp

            <div class="col-12 col-lg-8">
                <div class="permissions-card h-100">
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="mb-0 fw-semibold text-dark">
                                <i class="fas fa-key me-2 text-warning"></i>Permissions Management
                            </h5>
                            <div class="header-actions">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="expandAll">
                                    <i class="fas fa-expand-alt me-1"></i>Expand
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="collapseAll">
                                    <i class="fas fa-compress-alt me-1"></i>Collapse
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="search-section mb-4">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium text-dark mb-2">Search Permissions</label>
                                    <div class="search-input-group">
                                        <i class="fas fa-search search-icon"></i>
                                        <input type="text" class="form-control search-input" id="permSearch" placeholder="Type to filter permissions...">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bulk-actions">
                                        <button type="button" class="btn btn-success btn-sm" id="selectFiltered">
                                            <i class="fas fa-check-circle me-1"></i>Select Filtered
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm" id="clearFiltered">
                                            <i class="fas fa-times-circle me-1"></i>Clear Filtered
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="permissions-accordion" id="permAccordion">
                            @php
                            $icons = [
                                'add' => 'fa-plus-circle text-success',
                                'edit' => 'fa-edit text-warning',
                                'delete' => 'fa-trash-alt text-danger',
                                'create' => 'fa-file-alt text-primary',
                                'change' => 'fa-exchange-alt text-info',
                                'view' => 'fa-eye text-purple',
                                'refresh' => 'fa-sync-alt text-teal',
                                'urge' => 'fa-bolt text-pink',
                                'purge' => 'fa-ban text-danger',
                                'fetch' => 'fa-download text-indigo',
                                'misc' => 'fa-folder text-secondary'
                            ];
                            @endphp

                            @foreach($grouped as $group => $names)
                            @php sort($names); $gid = 'grp_'.$group; @endphp
                            <div class="permission-group mb-3">
                                <div class="group-header" id="h-{{ $gid }}">
                                    <button class="group-toggle collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c-{{ $gid }}">
                                        <div class="group-header-content">
                                            <div class="group-info">
                                                <i class="group-icon fas {{ $icons[$group] ?? 'fa-folder text-primary' }} me-2"></i>
                                                <span class="group-name">{{ str_replace('_',' ', ucfirst($group)) }}</span>
                                            </div>
                                            <div class="group-stats">
                                                <span class="group-count-badge">
                                                    <span class="group-count" data-group="{{ $gid }}">0</span>
                                                    <span class="text-muted">/ {{ count($names) }}</span>
                                                </span>
                                                <i class="fas fa-chevron-down toggle-icon"></i>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                                <div id="c-{{ $gid }}" class="accordion-collapse collapse">
                                    <div class="group-body">
                                        <div class="group-actions mb-3">
                                            <button type="button" class="btn btn-sm btn-outline-success group-select" data-target="{{ $gid }}">
                                                <i class="fas fa-check-square me-1"></i>Select All
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary group-clear" data-target="{{ $gid }}">
                                                <i class="fas fa-square me-1"></i>Clear All
                                            </button>
                                        </div>
                                        <div class="permission-grid" data-group="{{ $gid }}">
                                            @foreach($names as $n)
                                            @php $checked = in_array($n, $selected) ? 'checked' : ''; $safeId = 'perm_'.md5($n); @endphp
                                            <div class="permission-item" data-name="{{ strtolower($n) }}">
                                                <label for="{{ $safeId }}" class="permission-label">
                                                    <input class="permission-checkbox" type="checkbox" id="{{ $safeId }}" name="permission[]" value="{{ $n }}" {{ $checked }}>
                                                    <div class="permission-content">
                                                        <div class="permission-name">{{ $n }}</div>
                                                        <div class="permission-checkmark">
                                                            <i class="fas fa-check"></i>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="bottom-actions mt-4 pt-3 border-top">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="quick-actions">
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="expandAllBottom">
                                            <i class="fas fa-expand-alt me-1"></i>Expand All
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="collapseAllBottom">
                                            <i class="fas fa-compress-alt me-1"></i>Collapse All
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6 text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Save Role
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
(function(){
    const $=(s,r=document)=>r.querySelector(s);
    const $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
    const selectedCount=$('#selectedCount');
    const permSearch=$('#permSearch');
    const selectedChips=$('#selectedChips');
    const chipLimit=10;let showAllChips=false;

    function recalcSelected(){
        const on=$$('.permission-checkbox').filter(c=>c.checked);
        selectedCount.textContent=on.length;
        selectedChips.innerHTML='';
        const chipsToShow=showAllChips?on:on.slice(0,chipLimit);
        chipsToShow.forEach(cb=>{
            const chip=document.createElement('span');
            chip.className='chip';
            chip.dataset.value=cb.value;
            chip.innerHTML=`<span>${cb.value}</span><button type="button" class="x" aria-label="Remove">&times;</button>`;
            selectedChips.appendChild(chip);
        });
        if(on.length>chipLimit){
            const moreChip=document.createElement('span');
            moreChip.className='chip chip-expandable';
            moreChip.style.cursor='pointer';
            moreChip.innerHTML=showAllChips?`<i class="fas fa-chevron-up me-1"></i>Show less`:`<i class="fas fa-chevron-down me-1"></i>+${on.length-chipLimit} more`;
            moreChip.addEventListener('click',()=>{showAllChips=!showAllChips;recalcSelected();});
            selectedChips.appendChild(moreChip);
        }
        $$('.permission-grid').forEach(grid=>{
            const gid=grid.dataset.group;
            const badge=$(`.group-count[data-group="${gid}"]`);
            if(badge) badge.textContent=$$('.permission-checkbox:checked',grid).length;
        });
    }

    function filterPerms(){
        const q=permSearch.value.trim().toLowerCase();
        $$('.permission-grid').forEach(grid=>{
            let any=false;
            $$('.permission-item',grid).forEach(item=>{
                const match=item.dataset.name.includes(q);
                item.hidden=!match;
                if(match) any=true;
            });
            const collapse=grid.closest('.accordion-collapse');
            if(q&&any) collapse.classList.add('show');
        });
    }

    $$('.permission-checkbox').forEach(cb=>cb.addEventListener('change',()=>{showAllChips=false;recalcSelected();}));
    permSearch.addEventListener('input',filterPerms);

    $('#selectFiltered').addEventListener('click',()=>{
        $$('.permission-grid .permission-item:not([hidden]) .permission-checkbox').forEach(cb=>cb.checked=true);
        showAllChips=false;recalcSelected();
    });
    $('#clearFiltered').addEventListener('click',()=>{
        $$('.permission-grid .permission-item:not([hidden]) .permission-checkbox').forEach(cb=>cb.checked=false);
        showAllChips=false;recalcSelected();
    });

    $$('.group-select').forEach(b=>b.addEventListener('click',()=>{
        const grid=document.querySelector(`.permission-grid[data-group="${b.dataset.target}"]`);
        $$('.permission-checkbox',grid).forEach(cb=>cb.checked=true);
        showAllChips=false;recalcSelected();
    }));
    $$('.group-clear').forEach(b=>b.addEventListener('click',()=>{
        const grid=document.querySelector(`.permission-grid[data-group="${b.dataset.target}"]`);
        $$('.permission-checkbox',grid).forEach(cb=>cb.checked=false);
        showAllChips=false;recalcSelected();
    }));

    function expandAll(){ $$('.accordion-collapse').forEach(c=>c.classList.add('show')); }
    function collapseAll(){ if(!permSearch.value) $$('.accordion-collapse').forEach(c=>c.classList.remove('show')); }

    $('#expandAll').addEventListener('click',expandAll);
    $('#collapseAll').addEventListener('click',collapseAll);
    $('#expandAllBottom').addEventListener('click',expandAll);
    $('#collapseAllBottom').addEventListener('click',collapseAll);

    selectedChips.addEventListener('click',e=>{
        if(!e.target.classList.contains('x')) return;
        const val=e.target.parentElement.dataset.value;
        const cb=$(`.permission-checkbox[value="${CSS.escape(val)}"]`);
        if(cb){ cb.checked=false;recalcSelected(); }
    });

    recalcSelected();
})();
</script>
@endsection
