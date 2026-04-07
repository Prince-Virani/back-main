@extends('layout.default')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Native Ads</h3>
            @can('add-native-ads')
                <a href="{{ route('native_ads.create') }}" class="btn btn-success">+ New Ad</a>
            @endcan
        </div>

        <form method="GET" action="{{ route('native_ads.index') }}" class="mb-4 row g-2">
            <div class="col-auto">
                <select name="packagename" class="form-select" onchange="this.form.submit()">
                    <option value="">All Applications</option>
                    @foreach ($applicationList as $pkg => $appName)
                        <option value="{{ $pkg }}" {{ request('packagename') === $pkg ? 'selected' : '' }}>
                            {{ $appName }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if (request()->filled('packagename'))
                <div class="col-auto">
                    <a href="{{ route('native_ads.index') }}" class="btn btn-outline-secondary">
                        Clear Filter
                    </a>
                </div>
            @endif
        </form>

        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>App</th>
                            <th>Title</th>
                            <th>Media</th>
                            <th>Icon</th>
                            <th>CTA Link</th>
                            <th>Button</th>
                            @can('edit-native-ads')
                                <th>Status</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ads as $ad)
                                <tr>
                                    <td>{{ $ad->id }}</td>
                                    <td>{{ $applicationList[$ad->packagename] ?? $ad->packagename }}</td>
                                    <td>{{ $ad->title }}</td>
                                    <td><img src="{{ $ad->mediaurl }}" style="max-height:50px"></td>
                                    <td><img src="{{ $ad->icon }}" style="max-height:30px"></td>
                                    <td><a href="{{ $ad->calltoactionlink }}" target="_blank">Link</a></td>
                                    <td>{{ $ad->buttontext }}</td>
                                    @can('edit-native-ads')
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox"
                                                    id="statusSwitch-{{ $ad->id }}"
                                                    data-url="{{ route('native_ads.toggle', $ad) }}" onchange="toggleStatus(this)"
                                                    {{ $ad->status_flag ? 'checked' : '' }}>
                                                <label class="form-check-label" for="statusSwitch-{{ $ad->id }}">
                                                    {{ $ad->status_flag ? 'Active' : 'Inactive' }}
                                                </label>
                                            </div>
                                        </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No ads found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $ads->links() }}
                </div>
            </div>
        </div>
    @endsection

    @push('js')
        <script>
            function toggleStatus(el) {
                const url = el.dataset.url;
                const token = document.querySelector('meta[name="csrf-token"]').content;
                const wasOn = el.checked;

                fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        }
                    })
                    .then(res =>
                        res.json().then(data => ({
                            status: res.status,
                            body: data
                        }))
                    )
                    .then(({
                        status,
                        body
                    }) => {
                        const label = document.querySelector(`label[for="${el.id}"]`);
                        if (status === 200 && body.success) {
                            el.checked = !!body.status_flag;
                            label.textContent = body.status_flag ? 'Active' : 'Inactive';
                        } else {
                            el.checked = !wasOn;
                            alert(body.message || 'Toggle failed');
                        }
                    })
                    .catch(() => {
                        el.checked = !wasOn;
                        alert('Toggle failed');
                    });
            }
        </script>
    @endpush
