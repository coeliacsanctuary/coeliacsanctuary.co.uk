<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/>

    <meta name="author" content="Coeliac Sanctuary"/>

    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

    <meta name="google-site-verification" content="MkXdbyO1KF2xCS7VFkP7v5ZaWw3WObMUJDFxX0z7_4w"/>

    <meta property="article:publisher" content="https://www.facebook.com/CoeliacSanctuary"/>
    <meta property="og:updated_time" content="{{ date('c') }}"/>

    <link href="/assets/images/apple/apple-touch-icon-57x57.png" rel="apple-touch-icon"/>
    <link href="/assets/images/apple/apple-touch-icon-72x72.png" rel="apple-touch-icon" sizes="72x72"/>
    <link href="/assets/images/apple/apple-touch-icon-114x114.png" rel="apple-touch-icon" sizes="114x114"/>
    <link href="/assets/images/apple/apple-touch-icon-152x152.png" rel="apple-touch-icon" sizes="152x152"/>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap" rel="stylesheet">

    <script async src="https://www.googletagmanager.com/gtag/js?id=G-5PWV6VHY13"></script>

    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        gtag('js', new Date());

        gtag('config', 'G-5PWV6VHY13'); // GA-4
    </script>

    @vite('resources/js/app.ts')
    @inertiaHead
</head>
<body class="mb-0">
@inertia
@if(app()->isLocal())
    <div class="fixed bottom-0 right-0 bg-red text-white text-xs font-semibold leading-0 p-2">
        <span class="xxs:hidden">xxxs</span>
        <span class="hidden xxs:max-xs:block">xxs</span>
        <span class="hidden xs:max-sm:block">xs</span>
        <span class="hidden sm:max-md:block">sm</span>
        <span class="hidden md:max-xmd:block">md</span>
        <span class="hidden xmd:max-lg:block">xmd</span>
        <span class="hidden lg:max-xl:block">lg</span>
        <span class="hidden xl:max-2xl:block">xl</span>
        <span class="hidden 2xl:block">2xl</span>
    </div>
@endif
<script async defer src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>

<noscript>
    <img alt='' height="1" width="1" style="display:none"
         src="https://www.facebook.com/tr?id=376206517120953&ev=PageView&noscript=1"
    />
</noscript>

<script>
    setTimeout(() => {
        !(function (f, b, e, v, n, t, s) {
            if (f.fbq) return;
            n = f.fbq = function () {
                n.callMethod
                    ? n.callMethod.apply(n, arguments) : n.queue.push(arguments);
            };
            if (!f._fbq) f._fbq = n;
            n.push = n;
            n.loaded = !0;
            n.version = '2.0';
            n.queue = [];
            t = b.createElement(e);
            t.async = !0;
            t.src = v;
            s = b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t, s);
        }(window, document, 'script',
            'https://connect.facebook.net/en_US/fbevents.js'));
        fbq('init', '376206517120953');
        fbq('track', 'PageView');
    }, 5000);
</script>

</body>
</html>
