@extends('layout.default')

@section('title', 'API Keys')

@section('content')
    <div class="d-flex align-items-center mb-3">
        <div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">API Keys</a></li>
                <li class="breadcrumb-item active">Inactive Keys</li>
            </ul>
            <h1 class="page-header mb-0">🔐 Inactive API Keys</h1>
        </div>
        <div class="ms-auto">
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                <i class="fa fa-home me-1"></i> Dashboard
            </a>
        </div>
    </div>

    <div class="card">
        <div class="tab-content p-4">
            <div class="table-responsive">
                <table class="table table-hover text-nowrap align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Package Name</th>
                            <th>API Key</th>
                            @can('edit-inactive-api-keys')
                                <th>Action</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($apiKeys as $key)
                            <tr>
                                <td>{{ $key->id }}</td>
                                <td>{{ $key->package_name }}</td>
                                <td>
                                    <code class="text-muted">{{ Str::mask($key->api_key, '*', 4, -4) }}</code>
                                    <button class="btn btn-sm btn-outline-secondary ms-2"
                                        onclick="copyToClipboard('{{ $key->api_key }}')" title="Copy API Key">
                                        <i class="fa fa-copy"></i>
                                    </button>
                                </td>
                                @can('edit-inactive-api-keys')
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input status-toggle" type="checkbox" role="switch"
                                                data-id="{{ $key->id }}" data-package="{{ $key->package_name }}"
                                                data-apikey="{{ $key->api_key }}">
                                        </div>
                                    </td>
                                @endcan
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="fa fa-key fa-2x mb-2"></i>
                                    <p>No inactive API keys available.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($apiKeys->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $apiKeys->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.copyToClipboard = function(text) {
                navigator.clipboard.writeText(text);
            };

            document.querySelectorAll('.status-toggle').forEach(toggle => {
                toggle.addEventListener('change', function() {
                    const id = this.dataset.id;
                    const packageName = this.dataset.package;
                    const apiKey = this.dataset.apikey;

                    if (!this.checked) return; // ignore unchecking

                    fetch(`/api_keys/${id}/toggle-status`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                package_name: packageName,
                                apikey: apiKey,
                                status_flag: 1
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                // remove row from table
                                this.closest('tr').remove();
                            } else {
                                alert(data.error || 'Activation failed.');
                                this.checked = false;
                            }
                        })
                        .catch(() => {
                            alert('Network error. Please try again.');
                            this.checked = false;
                        });
                });
            });
        });
    </script>
@endpush

@push('css')
    <style>
        .table td code {
            font-size: 0.9em;
            background-color: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
        }
    </style>
@endpush
