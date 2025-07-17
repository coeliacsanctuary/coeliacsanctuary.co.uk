@php use App\DataObjects\NotificationRelatedObject; @endphp
<mjml>
    <mj-head>

        <mj-font name="Raleway" href="https://fonts.googleapis.com/css2?family=Raleway:wght@100..900&display=swap"/>

        <mj-style>
            a {color:black;font-weight:600;text-decoration:none}
            a:hover {text-decoration:underline;}
            h1 {font-size:25px;text-align:center;margin:0 0 10px; padding:0;color:#000;font-weight:600}
            h2 {font-size:20px;font-weight: 600;}
            h2,h3,h4 {margin:0;padding:0;}
            h4 {font-size:18px;font-weight:600;}
            strong {font-weight: 600!important;}

            @media only screen and (max-width: 480px) { *[class~=hide_on_mobile] { display: none !important;} }
            *[class~=force-half-width] { width:50% !important;}
            *[class~=red-text] { color:#E53E3E !important;}
        </mj-style>

        @stack('head')

        <mj-attributes>
            <mj-all font-family="Raleway, Arial" text-align="left" padding="0"/>
            <mj-text align="left" color="#555" font-size="16px" padding="0"/>
            <mj-section background-color="#fff" padding="10px"/>
            <mj-column padding="0"/>
            <mj-button background-color="#DBBC25" padding="0" font-size="15px" font-weight="bold"/>
            <mj-table font-size="15px"></mj-table>
            <mj-class name="blue" background-color="#80CCFC"></mj-class>
            <mj-class name="light-blue" background-color="#addaf9"></mj-class>
            <mj-class name="yellow" background-color="#DBBC25"></mj-class>
            <mj-class name="inner" padding="7px 0" line-height="1.2"></mj-class>
            <mj-class name="social" padding="0"></mj-class>
            <mj-class name="light-section" background-color="#e7f4fe" margin="-10px 0" padding="15px" />
        </mj-attributes>

    </mj-head>
    <mj-body background-color="#f7f7f7">
        <mj-container background-color="#f7f7f7">
            <mj-wrapper>
                @isset($key)
                    <mj-section mj-class="blue" padding="5px">
                        <mj-column>
                            <mj-text align="center" font-size="14px">Having trouble viewing this email? <a
                                    href="{{ config('app.url') }}/email/{{ $key }}">View Online</a></mj-text>
                        </mj-column>
                    </mj-section>
                @endisset
                <mj-section mj-class="blue">
                    <mj-column>
                        <mj-image width="300" src="{{ asset('images/email/logo.jpg') }}"></mj-image>
                    </mj-column>
                </mj-section>

                <mj-section mj-class="yellow" padding="8px 10px" vertical-align="middle">
                    <mj-column width="75%"  vertical-align="middle">
                        <mj-text>
                            @yield('header')
                        </mj-text>
                    </mj-column>
                    <mj-column width="25%" vertical-align="middle">
                        <mj-text align="right">
                            {{ $date->format('d/m/Y')  }}
                        </mj-text>
                    </mj-column>
                </mj-section>
            </mj-wrapper>
            <mj-wrapper>
                @yield('main-content')
            </mj-wrapper>
            @isset($relatedTitle, $relatedItems)
                <mj-wrapper>
                    @unless(isset($hideRelatedGap) && $hideRelatedGap === true))
                        <mj-section padding="20px 0 0">
                            <mj-column>
                                <mj-text>&nbsp;</mj-text>
                            </mj-column>
                        </mj-section>
                    @endunless
                    <mj-section padding="10px" padding-top="15px" background-color="#f0f0f0">
                        <mj-column>
                            <mj-text>
                                <h2>Have you seen these {{ $relatedTitle }}?</h2>
                            </mj-text>
                        </mj-column>
                    </mj-section>
                    <mj-section padding="10px" background-color="#f0f0f0">
                        @foreach($relatedItems as $related)
                            @php /** @var NotificationRelatedObject $related */ @endphp
                            <mj-column>
                                <mj-image
                                    padding="0 5px 5px"
                                    src="{{ $related->image }}"
                                    fluid-on-mobile="true"
                                    href="{{ $related->link }}"
                                />
                                <mj-text padding="0 5px 5px">
                                    <h3><a href="{{ $related->link }}">{{ $related->title }}</a></h3>
                                </mj-text>
                            </mj-column>
                        @endforeach
                    </mj-section>
                </mj-wrapper>
            @endisset
            <mj-wrapper>
                <mj-section mj-class="yellow" padding="5px"></mj-section>
                <mj-section mj-class="blue" padding="10px">
                    <mj-column>
                        @if(isset($reason))
                            <mj-text align="center">
                                This one off email was sent to
                                {{ isset($notifiable) ? $notifiable->email : $email }} {{ $reason }}.
                            </mj-text>
                            <mj-text>&nbsp;</mj-text>
                            <mj-text align="center">If you believe this message was sent in error, please
                                <a style="color:#000;font-weight:bold;text-decoration:none;" href="{{ config('app.url') }}/contact">contact us.</a>
                            </mj-text>
                        @else
                            @yield('footer')
                        @endif
                        <mj-text>&nbsp;</mj-text>
                        <mj-text align="center" font-size="14px">
                            CoeliacSanctuary.co.uk, PO Box 643, Crewe, Cheshire, CW1 9LJ, United Kingdom
                        </mj-text>
                    </mj-column>
                </mj-section>
            </mj-wrapper>
        </mj-container>
    </mj-body>
</mjml>
