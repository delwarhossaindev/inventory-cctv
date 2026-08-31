@php
    $user = auth()->user();

    // Keep only items the user is allowed to see.
    $visible = collect(config('menu', []))->map(function ($section) use ($user) {
        $section['items'] = collect($section['items'])
            ->filter(fn ($item) => empty($item['can']) || ($user && $user->can($item['can'])))
            ->map(function ($item) {
                $item['active'] = $item['active'] ?? $item['route'];
                $item['is_active'] = request()->routeIs($item['active']);
                // For items that share a route but differ by a query param (e.g. category levels),
                // only highlight the one matching the current ?level=.
                if ($item['is_active'] && isset($item['params']['level'])) {
                    $item['is_active'] = (string) request('level') === (string) $item['params']['level'];
                }
                return $item;
            })
            ->values();
        return $section;
    })->filter(fn ($section) => $section['items']->isNotEmpty())->values();
@endphp

<nav class="nav flex-column">
    @foreach ($visible as $i => $section)
        @php $sectionActive = $section['items']->contains('is_active', true); @endphp

        @if (empty($section['label']))
            {{-- Top-level items, no header --}}
            @foreach ($section['items'] as $item)
                @include('layouts._nav-link', ['item' => $item])
            @endforeach
        @else
            @php $collapseId = 'nav-section-' . $i; @endphp
            <button class="nav-section-label nav-section-toggle d-flex justify-content-between align-items-center w-100"
                    type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}"
                    aria-expanded="{{ $sectionActive ? 'true' : 'false' }}">
                <span>{{ $section['label'] }}</span>
                <i class="bi bi-chevron-down chevron"></i>
            </button>
            <div class="collapse {{ $sectionActive ? 'show' : '' }}" id="{{ $collapseId }}">
                @foreach ($section['items'] as $item)
                    @include('layouts._nav-link', ['item' => $item])
                @endforeach
            </div>
        @endif
    @endforeach
</nav>
