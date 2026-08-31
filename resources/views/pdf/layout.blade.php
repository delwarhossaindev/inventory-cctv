<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; }
        body { font-family: dejavusans, sans-serif; font-size: 11px; color: #111; }
        table { border-collapse: collapse; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .muted { color: #555; }
    </style>
    @stack('styles')
</head>
<body>
    {{-- Branch footer pinned to the bottom of every page --}}
    <htmlpagefooter name="jmfooter">
        @include('pdf.partials.footer')
    </htmlpagefooter>
    <sethtmlpagefooter name="jmfooter" value="on" />

    @include('pdf.partials.header')

    @yield('content')
</body>
</html>
