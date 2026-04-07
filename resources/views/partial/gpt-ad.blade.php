@if($ad 
    && !empty($ad->adunit_id) 
    && !empty($ad->ad_unit_code) 
    && !empty($ad->ad_unit_size)
)
    @php
        // Normalize size string into a PHP array
        $raw     = trim($ad->ad_unit_size);
        $wrapped = '[' 
                 . preg_replace('/,\s*$/','',preg_replace('/\s+/','',$raw)) 
                 . ']';
        $sizes   = json_decode($wrapped, true) ?: [];
        $path    = "/23313619427/{$ad->ad_unit_code}";
        $height  = $sizes[0][1] ?? 100; // fallback height
        $containerId = $overrideId ?? $ad->adunit_id;
        $isLazy      = $lazy ?? true;    
    @endphp

    <div
      id="{{ $containerId }}"
      class="gpt-ad-block"
      data-ad-unit="{{ $path }}"
      data-ad-sizes='@json($sizes)'
      data-lazy="{{ $isLazy ? 'true' : 'false' }}" 
      style="min-height:{{ $height }}px; text-align:center;"
    >
      {{-- Only show a spinner placeholder on lazy-loaded slots --}}
      @if($ad->is_lazy)
        <div class="ad-placeholder">⏳ Loading ad…</div>
      @endif
    </div>
@endif
