<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}"{{ (!empty($htmlAttribute)) ? $htmlAttribute : '' }}>
<head>
	@include('partial.head')
</head>
<body class="{{ (!empty($bodyClass)) ? $bodyClass : '' }}">
	@yield('content')

	@include('partial.scroll-top-btn')
	@include('partial.theme-panel')
	@include('partial.scripts')
</body>
</html>
