{{-- @pwaMeta must be placed inside <head>...</head> --}}

{{-- Manifest link --}}
<link
    @foreach($manifestLink as $attr => $value)
        {{ $attr }}="{{ $value }}"
    @endforeach
>

{{-- Theme & background --}}
<meta name="theme-color" content="{{ $manifest->get('theme_color', '#000000') }}">
<meta name="background-color" content="{{ $manifest->get('background_color', '#ffffff') }}">

{{-- iOS Safari --}}
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="{{ $manifest->get('short_name') }}">
<link rel="apple-touch-icon" href="{{ rtrim(config('pwa.icons.output_url_prefix'), '/') }}/apple-touch-icon.png">

{{-- Favicon --}}
@foreach(config('pwa.icons.favicon_sizes', [16, 32]) as $faviconSize)
    <link rel="icon" type="image/png" sizes="{{ $faviconSize }}x{{ $faviconSize }}"
          href="{{ rtrim(config('pwa.icons.output_url_prefix'), '/') }}/favicon-{{ $faviconSize }}x{{ $faviconSize }}.png">
@endforeach

{{-- Generic mobile --}}
<meta name="mobile-web-app-capable" content="yes">
<meta name="application-name" content="{{ $manifest->get('name') }}">
