<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="row align-items-center flex-row-reverse">
            <div class="col-md-12 col-sm-12 mt-3 mt-lg-0 text-center">
                Copyright © {{ $generalSetting?->developed_year ?: now()->year }}
                <span class="text-primary">{{ $generalSetting?->side_name ?: config('app.name') }}</span>.
                All rights reserved.
            </div>
        </div>
    </div>
</footer>
<!-- End Footer -->
