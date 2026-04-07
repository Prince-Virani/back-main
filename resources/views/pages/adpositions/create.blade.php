{{-- resources/views/admin/adpositions/create.blade.php --}}
@extends('layout.default')

@section('title', isset($adPosition) ? 'Edit Ad Position' : 'Add Ad Position')

@section('content')
    <div class="container">
        <h1 class="my-4">{{ isset($adPosition) ? 'Edit Ad Position' : 'Add Ad Position' }}</h1>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card">
            <div class="card-body pb-2">
                <form method="POST" action="{{ isset($adPosition) ? route('adpositions.update', $adPosition->id) : route('adpositions.store') }}">
                    @csrf
                    @if(isset($adPosition))
                        @method('PUT')
                    @endif

                    <div class="mb-3">
                        <label for="position_name" class="form-label">Position Name</label>
                        <input type="text" class="form-control" id="position_name" style="width: 300px;" name="position_name" value="{{ isset($adPosition) ? $adPosition->position_name : '' }}" required>
                    </div>

                    

                    <button type="submit" class="btn btn-theme">{{ isset($adPosition) ? 'Update' : 'Add' }} Position</button>
                    <button  class="btn btn-secondary" onclick="window.history.back()">Cancel</button>
                </form>
            </div>
        </div>
    </div>
@endsection
