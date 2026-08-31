@extends('pdf.layout')

@section('content')
{{--
    Expected data:
    $title   string
    $period  string|null  (e.g. "01 Jun 2026 — 29 Jun 2026")
    $head    array of column headers
    $align   array of 'left'|'right'|'center' (same length as $head)
    $rows    array of rows; each row is an array of pre-formatted strings
    $foot    array|null  totals row (same length as $head)
--}}
<div style="text-align:center; margin-bottom:6px;">
    <span style="font-size:15px; font-weight:bold;">{{ $title }}</span>
    @if (!empty($period))<div class="muted" style="font-size:10px;">Period: {{ $period }}</div>@endif
</div>

<table width="100%" style="border:1px solid #000; font-size:10px;" cellpadding="4">
    <thead>
        <tr style="background:#f0f0f0; font-weight:bold;">
            @foreach ($head as $i => $h)
                <td style="border:1px solid #000; text-align:{{ $align[$i] ?? 'left' }};">{{ $h }}</td>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $row)
            <tr>
                @foreach ($row as $i => $cell)
                    <td style="border:1px solid #000; text-align:{{ $align[$i] ?? 'left' }};">{!! $cell !!}</td>
                @endforeach
            </tr>
        @empty
            <tr><td colspan="{{ count($head) }}" style="border:1px solid #000; text-align:center; padding:12px;">No records found.</td></tr>
        @endforelse
    </tbody>
    @if (!empty($foot))
        <tfoot>
            <tr style="background:#f0f0f0; font-weight:bold;">
                @foreach ($foot as $i => $cell)
                    <td style="border:1px solid #000; text-align:{{ $align[$i] ?? 'left' }};">{!! $cell !!}</td>
                @endforeach
            </tr>
        </tfoot>
    @endif
</table>
@endsection
