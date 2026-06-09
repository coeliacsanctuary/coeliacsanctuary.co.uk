<html lang="en">
<head>
    @googlefonts
    @vite('resources/js/standalone.ts')
</head>
<body>
<div id="app">
    <article-button
        size="{{ request()->get('size', 'md') }}"
        theme="{{ request()->get('theme', 'primary') }}"
        href="{{ request()->get('href', '#') }}"
        wrapper-styles="{{ request()->get('wrapperStyles', '') }}"
        @if(request()->get('bold') === '1')
            bold
        @endif
        target="_blank"
    >
        {{ request()->get('label') ?? 'label' }}
    </article-button>
</div>
</body>
</html>
