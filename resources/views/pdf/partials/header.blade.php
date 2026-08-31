@php $c = config('company'); $logo = public_path($c['logo']); @endphp
<div style="text-align:center; border-bottom:2px solid #111; padding-bottom:6px; margin-bottom:8px;">
    <table align="center" style="margin:0 auto;"><tr>
        @if (is_file($logo))
            <td style="vertical-align:middle;"><img src="{{ $logo }}" height="56"></td>
        @endif
        <td style="vertical-align:middle; padding-left:12px;">
            <span style="font-size:32px; font-weight:bold; letter-spacing:2px; color:#111;">{{ $c['name'] }}</span>
        </td>
    </tr></table>
    <div style="font-weight:bold; font-size:12px; margin-top:4px;">{{ $c['address'] }}</div>
    <div style="font-weight:bold; font-size:11px;">Mob: {{ $c['phones'] }} &nbsp; | &nbsp; Email: {{ $c['email'] }}</div>
</div>
