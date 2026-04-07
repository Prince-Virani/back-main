@extends('layout.default')

@section('title', 'Categories')

@section('content')
    <div class="d-flex align-items-center mb-3">
        <div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Categories</a></li>
                <li class="breadcrumb-item active">All Categories</li>
            </ul>
            <h1 class="page-header mb-0">📁 Manage Categories</h1>
        </div>

        <div class="ms-auto">
            @can('add-categories')
                <a href="{{ route('categories.create') }}" class="btn btn-theme">
                    <i class="fa fa-plus-circle fa-fw me-1"></i> Add Category
                </a>
            @endcan
            <a href="{{ route('dashboard') }}" class="btn btn-secondary ms-2">
                <i class="fa fa-home fa-fw me-1"></i> Go to Dashboard
            </a>
        </div>
    </div>

    <div class="card">
        <div class="tab-content p-4">
            <div class="tab-pane fade show active" id="allTab">
                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover text-nowrap align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Category Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                                <tr id="row-{{ $category->id }}">
                                    <td>{{ $category->id }}</td>
                                    <td id="name-{{ $category->id }}" class="category-name">{{ $category->name }}</td>
                                    <td>
                                        @can('edit-categories')
                                            <button class="btn btn-theme" id="edit-btn-{{ $category->id }}"
                                                onclick="editCategory({{ $category->id }})">✏️ Edit</button>
                                        @endcan
                                        <button class="btn  btn-theme" id="update-btn-{{ $category->id }}"
                                            onclick="updateCategory({{ $category->id }})" style="display: none;">✅
                                            Update</button>

                                        <button class="btn  btn-secondary" id="cancel-btn-{{ $category->id }}"
                                            onclick="cancelEdit({{ $category->id }})" style="display: none;">❌
                                            Cancel</button>
                                        @can('delete-categories')
                                            <button class="btn  btn-danger" onclick="deleteCategory({{ $category->id }})">🗑️
                                                Delete</button>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">
                                        <div class="alert alert-warning mb-0">
                                            😔 No categories found. Hit that "Add Category" button!
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($categories->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $categories->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script src="/assets/js/categories.js?ts=<?= time() ?>"></script>
@endsection
