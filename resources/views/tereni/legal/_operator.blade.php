<p>
    <strong>{{ filled($operator['name']) ? $operator['name'] : $site['site_name'] ?? 'Tereni Medijane' }}</strong>
    @if (filled($operator['id'])) (PIB/MB: {{ $operator['id'] }})@endif
    @if (filled($operator['address']))<br>{{ $operator['address'] }}@endif
    @if (filled($operator['email']))<br>Imejl: <a href="mailto:{{ $operator['email'] }}">{{ $operator['email'] }}</a>@endif
    @if (filled($operator['phone']))<br>Telefon: {{ $operator['phone'] }}@endif
</p>
