@extends('layout.default')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-sm-12">
            <div class="card shadow-lg rounded-3">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">Add Vertical</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('verticals.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Vertical Name</label>
                            <input type="text" class="form-control" name="name" required placeholder="Enter Vertical name">
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-theme px-4">Save Vertical</button>
                            <a href="{{ route('verticals.index') }}" class="btn btn-secondary px-4">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
