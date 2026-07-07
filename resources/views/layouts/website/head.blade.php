<meta charset="UTF-8">
{{--  SEO --}}
<title>@yield('meta_title', 'Noura Consult')</title>
<meta name="description" content="@yield('meta_description', '')">
<meta name="keywords" content="@yield('meta_keywords', '')">

<meta name="viewport" content="width=device-width, initial-scale=1">

{{--  CSRF --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

@if(!empty($settings?->logo))
    <link rel="icon" href="{{ asset('uploads/general/' . $settings->logo) }}" type="image/png">
@endif

<!------------------------------ links ------------------------------>

<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
      integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
      crossorigin="anonymous" referrerpolicy="no-referrer" />

<link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.rtl.min.css"
      integrity="sha384-CfCrinSRH2IR6a4e6fy2q6ioOX7O6Mtm1L9vRvFZ1trBncWmMePhzvafv7oIcWiW"
      crossorigin="anonymous">

<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css">

<link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"
      type="text/css" />

<link rel="stylesheet" href="{{ asset('asset/website/assets/css/custom.css') }}" type="text/css" />

@stack('custom-css')

{!! $codeSnippet->header_code ?? '' !!}
