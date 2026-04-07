@extends('layout.default')

@section('title', 'Websites')

@section('content')
    <div class="d-flex align-items-center mb-3">
        <div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Websites</a></li>
                <li class="breadcrumb-item active">All Websites</li>
            </ul>
            <h1 class="page-header mb-0">🌐 Manage Websites</h1>
        </div>
        <div class="ms-auto d-flex align-items-center gap-2">
                <a href="{{ route('websites.create') }}" class="btn btn-theme">
                    <i class="fa fa-plus-circle fa-fw me-1"></i> Add Website
                </a>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary ms-2">
                <i class="fa fa-home fa-fw me-1"></i> Go to Dashboard
            </a>

                <button type="button" class="btn btn-dark" data-url="{{ route('websites.clearAllCache') }}" data-method="POST"
                    onclick="performAction(this)">
                    <span class="spinner-border spinner-border-sm d-none"></span>
                    <span class="action-text">Refresh All Cache</span>
                </button>
            <div id="ajaxAlertsContainer"></div>
        </div>
    </div>

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="tab-content p-4">
            <div class="tab-pane fade show active" id="allTab">
                <div class="table-responsive">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Website Name</th>
                                <th>Domain</th>
                                <th>Website Theme</th>
                                <!-- <th>Website Vertical</th> -->
                                <th>Visit Site</th>
                                <th>Logo</th>
                                <th>Ads Live</th>
                                <th>Analytics</th>
                                <th>Pause Cloudflare</th>
                                <th>Refresh Cache</th>
                                <th>View Cache</th>
                                <th>Purge Cloud</th>
                                <th>Edit</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($websites->count() > 0)
                                @foreach ($websites as $key => $website)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            @if ($website->favicon_path)
                                                <img src="{{ asset('storage/website-logo/' . $website->id . '/favicon.webp') }}"
                                                    alt="Favicon" width="16" height="16" class="me-2">
                                            @endif
                                            {{ $website->website_name }}
                                        </td>
                                        <td>{{ $website->domain }}</td>
                                        <td>{{ $website->website_theme }}</td>
                                        <!-- <td>{{ $website->website_vertical }}</td> -->
                                        <td>
                                            <a href="https://{{ $website->domain }}" target="_blank"
                                                class="btn btn-outline-theme">
                                                🌍 Visit
                                            </a>
                                        </td>
                                        <td>
                                            @if ($website->logo_path)
                                                <img src="{{ asset('storage/website-logo/' . $website->id . '/logo.webp') }}"
                                                    alt="Logo" width="50">
                                            @else
                                                No Logo
                                            @endif
                                        </td>
                                            <td>
                                                <form action="{{ route('websites.toggleAds', $website->id) }}" method="POST"
                                                    onChange="this.submit()">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input"
                                                            id="adsSwitch{{ $website->id }}" name="is_ads_live"
                                                            {{ $website->is_ads_live ? 'checked' : '' }}>
                                                        <label class="form-check-label"
                                                            for="adsSwitch{{ $website->id }}"></label>
                                                    </div>
                                                </form>
                                            </td>
                                            <td>
                                                <form action="{{ route('websites.toggleAnalytics', $website->id) }}"
                                                    method="POST"
                                                    @if (!$website->ga_property_id) onchange="this.submit()" @endif>
                                                    @csrf
                                                    @method('PATCH')

                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input"
                                                            id="gaSwitch{{ $website->id }}" name="ga_property_id"
                                                            @if ($website->ga_property_id) checked
										disabled @endif>
                                                        <label class="form-check-label"
                                                            for="gaSwitch{{ $website->id }}"></label>
                                                    </div>
                                                </form>
                                            </td>
                                            <td>
                                                <form action="{{ route('websites.pause-zone', $website->id) }}" method="POST"
                                                    onchange="this.submit()">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input"
                                                            id="pauseSwitch{{ $website->id }}" name="paused"
                                                            {{ $website->cloudflare_paused ? 'checked' : '' }}>
                                                        <label class="form-check-label"
                                                            for="pauseSwitch{{ $website->id }}"></label>
                                                    </div>
                                                </form>
                                            </td>
                                       
                                            <td class="text-center">
                                                <form action="{{ route('websites.clearCache', $website->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-light"
                                                        onclick="return confirm('Refresh cache for {{ $website->domain }}?')">
                                                        🔄
                                                    </button>
                                                </form>
                                            </td>
                                      
                                            <td class="text-center">
                                                <a href="{{ route('websites.viewCache', $website->id) }}" class="btn btn-info"
                                                    target="_blank">
                                                    👁
                                                </a>
                                            </td>
                                      
                                            <td class="text-center">
                                                <form action="{{ route('websites.purge-cache', $website) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-warning">
                                                        🗑️ Purge
                                                    </button>
                                                </form>
                                            </td>
                                      
                                            <td>
                                                <a href="{{ route('websites.edit', $website->id) }}"
                                                    class="btn btn-theme edit-btn">✏️ Edit</a>
                                            </td>
                                       
                                            <td>
                                                <form action="{{ route('websites.destroy', $website->id) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="status_flag"
                                                        value="{{ $website->status_flag == 0 ? 1 : 0 }}">
                                                    <button type="submit"
                                                        class="btn {{ $website->status_flag == 0 ? 'btn-danger' : 'btn-success' }}">
                                                        {{ $website->status_flag == 0 ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </form>
                                            </td>
                                       

                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="11" class="text-center text-muted">😔 No websites found. Click "Add
                                        Website" to get started.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $websites->links() }} {{-- For websites --}}
                </div>
            </div>
        </div>
    </div>
    <script>
        async function performAction(el) {
            const url = el.dataset.url;
            const method = el.dataset.method || 'POST';
            const token = document.querySelector('meta[name="csrf-token"]').content;
            const spinner = el.querySelector('.spinner-border');
            const text = el.querySelector('.action-text');
            const original = text.textContent;

            spinner.classList.remove('d-none');
            text.textContent = ' Processing…';
            el.disabled = true;

            try {
                const res = await fetch(url, {
                    method,
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const raw = await res.text();
                const body = raw ? JSON.parse(raw) : {};
                const ok = res.ok && (body.success === undefined ? true : !!body.success);
                const msg = body.message || (ok ? 'Action completed.' : 'Action failed.');

                const container = document.getElementById('ajaxAlertsContainer');
                container.innerHTML = `
      <div class="alert ${ok ? 'alert-success' : 'alert-danger'} alert-dismissible fade show" role="alert">
        ${msg}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>`;

                if (!ok) throw new Error(msg);
            } catch (err) {
                alert(err.message || 'Action failed');
            } finally {
                spinner.classList.add('d-none');
                text.textContent = original;
                el.disabled = false;
            }
        }
    </script>

@endsection
