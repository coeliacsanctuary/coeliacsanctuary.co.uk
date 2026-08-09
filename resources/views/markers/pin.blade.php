{{--
    The map marker for a single place.

    The teardrop is the original marker.svg path with its leading subpath - a
    reverse wound circle that punched a transparent hole through the head -
    removed, so the head is solid and can carry a glyph.

    The viewBox is cropped tight to the stroked pin, and the pin is symmetrical
    about its own centre line, so the tip sits exactly on the bottom centre.
    That lets OpenLayers anchor the marker at [0.5, 1] with no fudge factor.

    @var string $color
    @var string $glyph
--}}
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="150" viewBox="364 -139 1025 1537.5">
    <g transform="matrix(1,0,0,-1,364.47458,1270.2373)">
        <path
            d="m 1024,896 q 0,-109 -33,-179 L 627,-57 q -16,-33 -47.5,-52 -31.5,-19 -67.5,-19 -36,0 -67.5,19 Q 413,-90 398,-57 L 33,717 Q 0,787 0,896 q 0,212 150,362 150,150 362,150 212,0 362,-150 150,-150 150,-362 z"
            fill="{{ $color }}"
            stroke="#fff"
            stroke-width="30"
        />
    </g>

    {{--
        Centred on the pin head, which sits at (876.475, 374.237) with a radius
        of 512. The 30 wide stroke straddles that edge, so the coloured interior
        ends at 497. Note it is 512 and not 256 - 256 was the radius of the hole
        this path used to have punched through it, not of the head itself.

        Every glyph is normalised so that the circle inscribed in its 100x100
        viewBox is exactly the circle enclosing its artwork. That makes this
        scale read directly as how much of the head the glyph fills - 7.95 puts
        every glyph, round or wide, at 80% of the interior - and keeps it one
        set of coordinates for all of them, with no per glyph offsets.

        The glyphs paint with currentColor, so the colour is set here rather
        than as a fill, which would not reach a glyph drawn with strokes.
    --}}
    <g transform="translate(478.975 -23.263) scale(7.95)" color="#fff">
        @include($glyph)
    </g>
</svg>
