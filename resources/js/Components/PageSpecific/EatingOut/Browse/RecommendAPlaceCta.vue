<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { PlusIcon } from '@heroicons/vue/24/solid';
</script>

<template>
  <Link
    class="group absolute bottom-0 left-2 z-10 flex items-center"
    href="/wheretoeat/recommend-a-place"
  >
    <div
      class="relative m-4 ml-1 cursor-pointer rounded-full border-2 border-white bg-secondary p-3 text-white shadow-sm transition md:m-6 md:shadow-lg"
    >
      <PlusIcon
        class="h-8 w-8 md:max-xmd:h-12 md:max-xmd:w-12 xmd:h-14 xmd:w-14"
      />
    </div>

    <div
      class="pointer-events-none absolute left-[4.5rem] max-w-[150px] rounded-full border-2 border-white bg-secondary px-4 py-1 text-center text-sm leading-none font-semibold uppercase opacity-0 transition-all duration-300 group-hover:opacity-70 group-hover:delay-500 md:max-xmd:left-[6.5rem] xmd:left-[7.5rem] xmd:text-base"
    >
      Recommend a Place
    </div>
  </Link>
</template>
