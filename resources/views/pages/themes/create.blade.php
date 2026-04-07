@extends('layout.default')

@section('content')
<div class="container">
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
    <div class="row justify-content-center">
        <div class="col-md-6 col-sm-12">
            <div class="card shadow-lg rounded-3">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">Add Theme Name</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('themes.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="themename" class="form-label">Theme Name</label>
                            <input type="text" class="form-control" name="themename" required placeholder="Enter category name">
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-theme px-4">Save Theme</button>
                            <a href="{{ route('themes.index') }}" class="btn btn-secondary px-4">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
