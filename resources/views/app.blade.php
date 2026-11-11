<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.app-head')

    @googlefonts
    @preloadImage

    @if (app()->isProduction())
        @include('partials.google-analytics')
    @endif

    @if (app()->isProduction() && config('coeliac.show_ads'))
        @include('partials.mediavine')
    @endif

    @vite('resources/js/app.ts')

    @inertiaHead
</head>
<body class="mb-0 {{ data_get($page, 'props.meta.bodyClass') }}">
    @inertia

    @if ( ! app()->isProduction())
        @include('partials.debug')
    @endif

    @if (app()->isProduction())
        @include('partials.facebook-pixel')
    @endif

    @journeyTracker
</body>
</html>
