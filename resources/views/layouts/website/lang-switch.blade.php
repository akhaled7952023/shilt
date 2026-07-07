{{-- <li class="nav-item">
    @php
        $current = LaravelLocalization::getCurrentLocale();

        if ($current === 'ar') {
            $switchTo = 'en';
            $label = 'English';
            $flagClass = 'flag-icon-gb';
        } else {
            $switchTo = 'ar';
            $label = 'العربية';
            $flagClass = 'flag-icon-sa';
        }
    @endphp

    <a class="nav-link nav-link-custome d-flex align-items-center"
       href="{{ LaravelLocalization::getLocalizedURL($switchTo, null, [], true) }}">
        <span class="flag-icon {{ $flagClass }}" style="margin-right:8px;"></span>
        {{ $label }}
    </a>
</li> --}}
