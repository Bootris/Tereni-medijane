<p>
    <strong>{{ filled($operator['name']) ? $operator['name'] : 'Saradnja nove generacije, Niš' }}</strong>
    @if (filled($operator['id'])) (PIB/MB: {{ $operator['id'] }})@endif
    @if (filled($operator['address']))<br>{{ $operator['address'] }}@endif
    @if (filled($operator['email']))<br>Imejl: <a href="mailto:{{ $operator['email'] }}">{{ $operator['email'] }}</a>@endif
    @if (filled($operator['phone']))<br>Telefon: {{ $operator['phone'] }}@endif
</p>
