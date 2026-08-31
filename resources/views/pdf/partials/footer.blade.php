@php $c = config('company'); @endphp
<div style="border-top:1px solid #999; padding-top:4px;">
    @if (!empty($c['nb']))
        <div style="text-align:center; font-weight:bold; font-size:11px; margin-bottom:4px;">{{ $c['nb'] }}</div>
    @endif
    <table width="100%" style="font-size:9px; text-align:center;">
        <tr>
            @foreach ($c['branches'] as $b)
                <td width="{{ intval(100 / max(count($c['branches']), 1)) }}%" style="vertical-align:top;">
                    <div style="font-weight:bold;">{{ $b['title'] }}</div>
                    @foreach ($b['lines'] as $line)
                        <div>{{ $line }}</div>
                    @endforeach
                </td>
            @endforeach
        </tr>
    </table>
</div>
