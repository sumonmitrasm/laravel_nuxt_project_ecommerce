<div class="btn-group flex-wrap">
    <a data-ajax-page href="{{ route('visitors.index') }}"
        class="btn btn-sm {{ request()->routeIs('visitors.index') ? 'btn-primary' : 'btn-outline-primary' }}">Visitor
        report</a>
    <a data-ajax-page href="{{ route('visitors.live') }}"
        class="btn btn-sm {{ request()->routeIs('visitors.live') ? 'btn-primary' : 'btn-outline-primary' }}">Live
        visitors</a>
    <a data-ajax-page href="{{ route('visitors.traffic-sources') }}"
        class="btn btn-sm {{ request()->routeIs('visitors.traffic-sources') ? 'btn-primary' : 'btn-outline-primary' }}">Traffic
        sources</a>
    <a data-ajax-page href="{{ route('visitors.countries') }}"
        class="btn btn-sm {{ request()->routeIs('visitors.countries') ? 'btn-primary' : 'btn-outline-primary' }}">Countries
        &amp; cities</a>
</div>
