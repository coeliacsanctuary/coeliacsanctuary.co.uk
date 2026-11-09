<div @class([
    'z-999 w-full fixed bottom-0 left-0 flex p-2 justify-between text-xs leading-0 font-semibold text-white',
    'bg-red' => app()->isLocal(),
    'bg-amber-500' => app()->environment('develop'),
    'bg-green-500' => app()->environment('preview'),
])>
    <div></div>
    <div>{{ app()->environment() }}</div>

    <div class="flex gap-2">
        <div>Breakpoint</div>
        <div>
            <span class="xxs:hidden">xxxs</span>
            <span class="xxs:max-xs:block hidden">xxs</span>
            <span class="xs:max-sm:block hidden">xs</span>
            <span class="hidden sm:max-md:block">sm</span>
            <span class="md:max-xmd:block hidden">md</span>
            <span class="xmd:max-lg:block hidden">xmd</span>
            <span class="hidden lg:max-xl:block">lg</span>
            <span class="hidden xl:max-2xl:block">xl</span>
            <span class="hidden 2xl:block">2xl</span>
        </div>
    </div>
</div>
