<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}"{{ (!empty($htmlAttribute)) ? $htmlAttribute : '' }}>
<head>
	@include('partial.head')
	<meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="{{ (!empty($bodyClass)) ? $bodyClass : '' }}">
	<!-- BEGIN #app -->
	<div id="app" class="app {{ (!empty($appClass)) ? $appClass : '' }}">
	  @includeWhen(empty($appHeaderHide), 'partial.header')
		@includeWhen(empty($appSidebarHide), 'partial.sidebar')
		@includeWhen(!empty($appTopNav), 'partial.top-nav')

		@if (empty($appContentHide))
			<!-- BEGIN #content -->
			<div id="content" class="app-content  {{ (!empty($appContentClass)) ? $appContentClass : '' }}">
				@yield('content')
			</div>
			<!-- END #content -->
		@else
    	@yield('content')
		@endif

		@includeWhen(!empty($appFooter), 'partial.footer')
	</div>
	<!-- END #app -->

	@yield('outter_content')
	@include('partial.scroll-top-btn')
	@include('partial.theme-panel')
	@include('partial.scripts')
</body>
</html>
