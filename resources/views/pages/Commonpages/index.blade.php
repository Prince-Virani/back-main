@extends('layout.default')

@section('title', 'Common Pages')

@section('content')
<div class="d-flex align-items-center mb-3">
	<div>
		<ul class="breadcrumb">
			<li class="breadcrumb-item"><a href="#">Common Pages</a></li>
			<li class="breadcrumb-item active">All Pages</li>
		</ul>
		<h1 class="page-header mb-0">📄 Manage Common Pages</h1>
	</div>
	<div class="ms-auto">
		@can('add-common-pages')
		<a href="{{ route('Commonpages.create') }}" class="btn btn-theme">
			<i class="fa fa-plus-circle fa-fw me-1"></i> Add Common Page
		</a>
		@endcan
		<a href="{{ route('dashboard') }}" class="btn btn-secondary ms-2">
			<i class="fa fa-home fa-fw me-1"></i> Go to Dashboard
		</a>
	</div>
</div>

<div class="card">
	<ul class="nav nav-tabs nav-tabs-v2 px-4">
		<li class="nav-item me-3"><a href="#allTab" class="nav-link active px-2" data-bs-toggle="tab" onclick="fetchPages(document.getElementById('websiteFilter').value, 2)">All</a></li>
		<li class="nav-item me-3"><a href="#allTab" class="nav-link px-2" data-bs-toggle="tab" onclick="fetchPages(document.getElementById('websiteFilter').value, 0)">Active</a></li>
		<li class="nav-item me-3"><a href="#allTab" class="nav-link px-2" data-bs-toggle="tab" onclick="fetchPages(document.getElementById('websiteFilter').value, 1)">Inactive</a></li>
	</ul>

	<div class="tab-content p-4">
		<div class="tab-pane fade show active" id="allTab">
			<!-- Filters & Search -->
			<div class="input-group mb-4">
				<button class="btn btn-default dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Filter Pages</button>
				<div class="dropdown-menu p-3" style="min-width: 300px;">
					<div class="mb-3">
						<label class="form-label">🌐 Website</label>
						<select id="websiteFilter" class="form-select" onchange="fetchPages(this.value, document.getElementById('statusFilter').value)">
							<option value="0" selected>All Websites</option>
							@foreach ($websites as $website)
								<option value="{{ $website->id }}">{{ $website->website_name }}</option>
							@endforeach
						</select>
					</div>
					<div>
						<label class="form-label">📌 Status</label>
						<select id="statusFilter" class="form-select" onchange="fetchPages(document.getElementById('websiteFilter').value, this.value)">
							<option value="0" selected>Active</option>
							<option value="1">Inactive</option>
						</select>
					</div>
				</div>

				<div class="flex-fill position-relative z-1 ms-2">
					<div class="input-group">
						<div class="input-group-text position-absolute top-0 bottom-0 bg-none border-0" style="z-index: 1020;">
							<i class="fa fa-search opacity-5"></i>
						</div>
						<input type="text" class="form-control ps-35px" id="searchInput" onkeyup="searchPages(this.value)" placeholder="Search pages...">
					</div>
				</div>
			</div>

			<!-- Table -->
			<div class="table-responsive">
				<table class="table table-hover text-nowrap">
					<thead>
						<tr>
							<th>ID</th>
							<th>Common Page Name</th>
							<th>🌐 Website</th>
							<th>URL</th>
							<th>Edit</th>
							<th>Delete</th>
						</tr>
					</thead>
					<tbody id="pagesTableBody">
						<!-- AJAX loaded content -->
					</tbody>
				</table>
			</div>

			<!-- Pagination -->
			<div class="d-md-flex align-items-center mt-3">
				<div class="me-md-auto text-md-left text-center mb-2 mb-md-0" id="entryCountText">
					<!-- Entries text will be injected here -->
				</div>
				<ul class="pagination mb-0 justify-content-center" id="paginationWrapper">
					<!-- AJAX pagination will be injected here -->
				</ul>
			</div>
		</div>
	</div>
</div>
<input type="hidden" id="currentPage" value="1">

<script>
	const indexpagesUrl = "{{ url('Commonpages/data') }}";
	const pagesUrl = "{{ url('Commonpages') }}";
	window.userPermissions = @json(auth()->user()->getAllPermissions()->pluck('name'));
</script>

<script src="/assets/js/Commonpages.js?ts=<?= time() ?>"></script>

@endsection
