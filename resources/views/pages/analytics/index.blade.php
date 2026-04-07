@extends('layout.default', [
	'appClass' => 'app-content-full-height',
	'appContentClass' => 'py-3'
])

@section('title', 'Google Analytics - Page Level Report')

@push('css')
<style>
.page-name-col {
    width: 300px !important;
    min-width: 300px !important;
    max-width: 300px !important;
    white-space: break-spaces;
}
    .dtfc-fixed-left,
    .dtfc-fixed-left .dtfc-fixed-wrapper {
    z-index: 2 !important;
    background-color: #fff !important; 
    box-shadow: 2px 0 5px rgba(0,0,0,0.05); 
    }

    .dtfc-fixed-left td,
    .dtfc-fixed-left th {
        background-color: #fff !important;
        z-index: 3 !important;
    }
</style>

	<link href="/assets/plugins/datatables.net-bs5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
	<link href="/assets/plugins/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css" rel="stylesheet">
	<link href="/assets/plugins/datatables.net-fixedcolumns-bs5/css/fixedColumns.bootstrap5.min.css" rel="stylesheet">
	<link href="/assets/plugins/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css" rel="stylesheet">
	<link href="/assets/plugins/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">
@endpush

@push('js')
	<script src="/assets/plugins/datatables.net/js/dataTables.min.js"></script>
	<script src="/assets/plugins/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
	<script src="/assets/plugins/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="/assets/plugins/moment/min/moment.min.js"></script>
    <script src="/assets/plugins/jszip/dist/jszip.min.js"></script>
	<script src="/assets/plugins/pdfmake/build/pdfmake.min.js"></script>
	<script src="/assets/plugins/pdfmake/build/vfs_fonts.js"></script>
	<script src="/assets/plugins/datatables.net-buttons/js/buttons.colVis.min.js"></script>
	<script src="/assets/plugins/datatables.net-buttons/js/buttons.html5.min.js"></script>
	<script src="/assets/plugins/datatables.net-buttons/js/buttons.print.min.js"></script>
	<script src="/assets/plugins/datatables.net-buttons-bs5/js/buttons.bootstrap5.min.js"></script>
	<script src="/assets/plugins/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
	<script src="/assets/plugins/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js"></script>
	<script src="/assets/plugins/datatables.net-fixedcolumns/js/dataTables.fixedColumns.min.js"></script>
	<script src="/assets/plugins/datatables.net-fixedcolumns-bs5/js/fixedColumns.bootstrap5.min.js"></script>
    <script src="/assets/plugins/bootstrap-daterangepicker/daterangepicker.js"></script>
	<script src="/assets/js/demo/GoogleAnalyticsData.js?ts=<?= time() ?>"></script>
@endpush

@section('content')
@if ($errors->count() > 0)
    <div class="row justify-content-center">
        <div class="alert alert-danger">
            @foreach ($errors as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    </div>
@endif
<div class="row g-3 align-items-end mb-4">
    <div class="col-md-3">
        <label class="form-label" for="websiteSelect">Website</label>
        <select class="form-select" name="website_id" id="websiteSelect" required>
            @foreach($websites as $website)
                <option value="{{ $website->ga_property_id }}">
                    {{ $website->website_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-5">
		<label class="form-label">Analytics Date Ranges</label>
		<div id="advance-daterange" class="btn btn-theme d-flex align-items-center text-start">
		<span class="text-truncate">&nbsp;</span>
		<i class="fa fa-caret-down ms-auto"></i>
		</div>
	</div>

    <div class="col-md-1 text-end">
        <button type="button" id="submitBtn" onclick="handleRenderTableData()" class="btn btn-theme w-100">
            🔍 View
        </button>
    </div>
</div>

    <div class="table-responsive" data-id="table">
        <table id="analyticsTable" class="table table-bordered table-xs w-100 text-nowrap mb-3 bg-component">
            <thead class="table-light text-center">
                <tr>
                    <th class="text-start page-name-col">Page Name</th>
                    <th>Views</th>
                    <th>Users</th>
                    <th>Views/User</th>
                    <th>Engagement (s)</th>
                    <th>Events</th>
                    <th>Ad Clicks</th>
                    <th>Ad Impressions</th>
                    <th>Payout</th>
                    <th>Revenue</th>
                </tr>
            </thead>
            <tbody class="text-center">
                <!-- Data will be populated by JavaScript after AJAX call -->
            </tbody>
             <tfoot class="text-center">
                <tr>
                    <th class="text-start">Total</th>
                    <th colspan="7"></th>
                    <th id="totalpayoutCell" class="fw-bold text-success"></th>
                    <th id="totalRevenueCell" class="fw-bold text-success"></th>
                </tr>
              </tfoot>
        </table>

		 
    </div>  <!-- No Data Message -->
		   <div id="noDataMessage" class="d-none text-center ">
        <p>No data available for the selected date range.</p>
    </div>
   <input type="hidden" id="start_date" name="start_date">
<input type="hidden" id="end_date" name="end_date">
@endsection