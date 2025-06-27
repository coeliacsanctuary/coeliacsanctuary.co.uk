<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { PlusIcon } from '@heroicons/vue/24/solid';
</script>

<template>
  <Link
    class="group absolute bottom-0 left-2 z-10 flex items-center p-4 select-none md:p-6"
    href="/wheretoeat/recommend-a-place"
  >
    <div
      class="relative -ml-3 cursor-pointer rounded-full border-2 border-white bg-secondary p-3 text-white shadow-sm transition md:shadow-lg"
    >
      <PlusIcon
        class="h-8 w-8 xmd:h-14 xmd:w-14 md:max-xmd:h-12 md:max-xmd:w-12"
      />
    </div>

    <div
      class="ml-2 max-w-[150px] rounded-full border-2 border-white bg-secondary px-4 py-1 text-center text-sm leading-none font-semibold uppercase opacity-0 transition-all duration-300 group-hover:opacity-70 group-hover:delay-500 xmd:text-base"
    >
      Recommend a Place
    </div>
  </Link>
</template>
