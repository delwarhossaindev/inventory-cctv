<a class="nav-link {{ $item['is_active'] ? 'active' : '' }}" href="{{ route($item['route'], $item['params'] ?? []) }}">
    <i class="bi {{ $item['icon'] }}"></i><span>{{ $item['label'] }}</span>
</a>
