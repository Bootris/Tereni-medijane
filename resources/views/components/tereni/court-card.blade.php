{{-- One court card — used by the map page (with :idx for marker wiring) and
     the /tereni directory. Expects the Court::toCard() array shape. --}}
@props(['court', 'idx' => null])

<li class="card court-card" @if ($idx !== null) data-idx="{{ $idx }}" @endif
    data-type="{{ $court['type'] }}" data-access="{{ $court['access'] }}"
    data-issue="{{ $court['has_issue'] ? 1 : 0 }}"
    data-text="{{ mb_strtolower($court['name'].' '.($court['facility'] ?? '')) }}">
    @if (!empty($court['photos']))
        {{-- Opens the gallery viewer; plain link (new tab) without JS. --}}
        <a href="{{ $court['photos'][0] }}" target="_blank" rel="noopener" class="court-cover"
            data-photos="{{ json_encode($court['photos']) }}"
            data-title="{{ $court['icon'] }} {{ $court['name'] }}"
            aria-label="Pogledaj fotografije: {{ $court['name'] }}">
            <img src="{{ $court['photos'][0] }}" alt="{{ $court['name'] }}" loading="lazy">
            @if (count($court['photos']) > 1)
                <span class="court-photo-count">📷 {{ count($court['photos']) }}</span>
            @endif
        </a>
    @endif
    <div class="court-card-body">
        <a href="{{ $court['url'] }}" class="court-name">{{ $court['icon'] }} {{ $court['name'] }}</a>
        <div class="muted" style="font-size:.85rem">
            @if ($court['facility']){{ $court['facility'] }} · @endif{{ $court['type_label'] }} · {{ $court['access_label'] }}
        </div>
        @if ($court['has_issue'])
            <div><span class="issue-flag">⚠ prijavljen problem</span></div>
        @endif
    </div>
</li>
