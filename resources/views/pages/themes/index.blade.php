@extends('layout.default')

@section('title', 'Themes')

@section('content')
    <div class="d-flex align-items-center mb-3">
        <div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Themes</a></li>
                <li class="breadcrumb-item active">All Themes</li>
            </ul>
            <h1 class="page-header mb-0">🎨 Manage Themes</h1>
        </div>

        <div class="ms-auto">
            @can('add-theme')
                <a href="{{ route('themes.create') }}" class="btn btn-theme">
                    <i class="fa fa-plus-circle fa-fw me-1"></i> Add Theme
                </a>
            @endcan
            <a href="{{ route('dashboard') }}" class="btn btn-secondary ms-2">
                <i class="fa fa-home fa-fw me-1"></i> Go to Dashboard
            </a>
        </div>
    </div>

    <div class="card">
        <div class="tab-content p-4">
            <div class="tab-pane fade show active" id="allThemesTab">
                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Theme Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($themes as $theme)
                                <tr id="row-{{ $theme->id }}">
                                    <td>{{ $theme->id }}</td>
                                    <td id="themename-{{ $theme->id }}" class="theme-name">{{ $theme->themename }}</td>
                                    <td>
                                        @can('edit-theme')
                                            <button class="btn btn-theme" id="edit-btn-{{ $theme->id }}"
                                                onclick="edittheme({{ $theme->id }})">✏️ Edit</button>
                                        @endcan
                                        <button class="btn  btn-theme" id="update-btn-{{ $theme->id }}"
                                            onclick="updatetheme({{ $theme->id }})" style="display: none;">✅
                                            Update</button>

                                        <button class="btn  btn-secondary" id="cancel-btn-{{ $theme->id }}"
                                            onclick="cancelEdit({{ $theme->id }})" style="display: none;">❌
                                            Cancel</button>

                                        @can('edit-theme-status')
                                            <button class="btn {{ $theme->status_flag == 0 ? 'btn-danger' : 'btn-success' }}"
                                                onclick="deletetheme({{ $theme->id }}, {{ $theme->status_flag == 0 ? 1 : 0 }})">
                                                {{ $theme->status_flag == 0 ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $themes->links() }} {{-- For themes --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="/assets/js/themes.js?ts=<?= time() ?>"></script>
@endpush
