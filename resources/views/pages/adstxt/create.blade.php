
@extends('layout.default')

@section('title', isset($ad) ? 'Edit Ads txt' : 'Create Ads txt')

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
                    {{ isset($adtxt) ? 'Edit Ad' : 'Create Ads txt' }} <small>{{ isset($adtxt) ? 'Update the ads txt details' : 'Fill out the form to create a new ads txt' }}</small>
                </h1>
                <hr class="mb-4">

                <!-- BEGIN #formControls -->
                <div id="formControls" class="mb-5">
                    <h4>Ads txt Details</h4>
                    <p>Please fill in the details for the ads txt below.</p>
                    <div class="card">
                        <div class="card-body pb-2">
                            <form method="POST" action="{{ isset($adtxt) ? route('adstxt.update', $adtxt->id) : route('adstxt.store') }}">
                                @csrf
                                @if(isset($adtxt))
                                    @method('PUT') <!-- Use PUT method for updating -->
                                @endif

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" for="websiteSelect">Website</label>
                                        <select class="form-select" name="website_id" id="websiteSelect" required>
                                            @foreach($websites as $website)
                                                <option value="{{ $website->id }}" {{ isset($adtxt) && $adtxt->website_id == $website->id ? 'selected' : '' }}>
                                                    {{ $website->website_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                   

                                    

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label" for="ContentTextarea">Ads Txt Content</label>
                                        <textarea class="form-control" name="content" id="ContentTextarea" required placeholder="Enter the ads txt here">{{ isset($adtxt) ? $adtxt->content : '' }}</textarea>
                                    </div>

                                   
                                </div>

                                <button type="submit" class="btn btn-theme">{{ isset($adtxt) ? 'Update' : 'Save' }}</button>
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
