<rss xmlns:atom="http://www.w3.org/2005/Atom" version="2.0">
    <channel>
        <title>{{ $title }}</title>
        <link>{{ $link }}</link>
        <description>{{ $description }}</description>
        <language>en-gb</language>
        <copyright>Copyright 2014 - {{ Carbon\Carbon::now()->format('Y') }} Coeliac Sanctuary</copyright>
        <webMaster>contact@coeliacsanctuary.co.uk (Coeliac Sanctuary)</webMaster>
        <lastBuildDate>{{ $date }}</lastBuildDate>
        <atom:link href="{{ $link }}/feed" rel="self" type="application/rss+xml"/>
        @foreach($items as $item)
            <item>
                @foreach($item as $tag => $data)
                    <{{ $tag }}
                    @isset($data['params'])
                        @foreach($data['params'] as $param => $value)
                            {{ $param }}="{{ $value }}"
                        @endforeach
                    @endisset

                    @isset($data['short'])
                        />
                    @else
                        >
                    @endisset

                    @if(!isset($data['short']))
                        @isset($data['cdata'])
                            <![CDATA[
                        @endisset
                        {{ $data['value'] }}
                        @isset($data['cdata'])
                            ]]>
                        @endisset
            </{{ $tag }}>
            @endif
            @endforeach
            </item>
        @endforeach
    </channel>
</rss>
