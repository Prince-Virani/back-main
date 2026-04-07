
@extends('layout.default')

@section('title', isset($tagmanager) ? 'Edit Tag Manager' : 'Create Tag Manager')

@push('js')
    <script src="/assets/js/demo/highlightjs.demo.js"></script>
    <script src="/assets/js/demo/sidebar-scrollspy.demo.js"></script>
@endpush

@section('content')
    <!-- BEGIN container -->
    <div class="container">
        <!-- BEGIN row -->
        <div class="row justify-content-center">
            <!-- BEGIN col-10 -->
            <div class="col-xl-10">
                <h1 class="page-header">
                    {{ isset($tagmanager) ? 'Edit Tag Manager' : 'Create Tag Manager' }} <small>{{ isset($tagmanager) ? 'Update the Tag Manager details' : 'Fill out the form to create a new Tag Manager' }}</small>
                </h1>
                <hr class="mb-4">

                <!-- BEGIN #formControls -->
                <div id="formControls" class="mb-5">
                    <h4>Tag Manager Details</h4>
                    <p>Please fill in the details for the Tag Manager below.</p>
                    <div class="card">
                        <div class="card-body pb-2">
                            <form method="POST" action="{{ isset($tagmanager) ? route('tagmanagers.update', $tagmanager->id) : route('tagmanagers.store') }}">
                                @csrf
                                @if(isset($tagmanager))
                                    @method('PUT') <!-- Use PUT method for updating -->
                                @endif

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" for="websiteSelect">Website</label>
                                        <select class="form-select" name="website_id" id="websiteSelect" required>
                                            @foreach($websites as $website)
                                                <option value="{{ $website->id }}" {{ isset($tagmanager) && $tagmanager->website_id == $website->id ? 'selected' : '' }}>
                                                    {{ $website->website_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label" for="contentTextarea">Tag Manager Content</label>
                                        <textarea class="form-control" name="content" id="contentTextarea" required placeholder="Enter the Tag Manager Content here">{{ isset($tagmanager) ? $tagmanager->content : '' }}</textarea>
                                    </div>

                                  
                                </div>

                                <button type="submit" class="btn btn-theme">{{ isset($tagmanager) ? 'Update' : 'Save' }}</button>
                                <button class="btn btn-secondary" onclick="window.history.back()">Cancel</button>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- END #formControls -->
            </div>
            <!-- END col-10 -->
        </div>
        <!-- END row -->
    </div>
    <!-- END container -->
@endsection
