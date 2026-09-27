<script setup lang="ts">
import { Bars3BottomLeftIcon } from '@heroicons/vue/24/outline';
import { ref } from 'vue';
import MobileNav from '@/Layouts/Components/Nav/MobileNav.vue';
import CoeliacMetas from '@/Layouts/Components/CoeliacMetas.vue';
import Sealiac from '@/Svg/Sealiac.vue';
import { Link } from '@inertiajs/vue3';
import { MetaProps } from '@/types/DefaultProps';

defineProps<{ metas: MetaProps }>();

const mobileNavOpen = ref(false);
</script>

<template>
  <CoeliacMetas :metas="metas" />

  <div class="main-header relative bg-primary shadow-lg">
    <div
      id="header"
      class="relative z-20"
    >
      <div
        class="relative mx-auto flex w-full items-center justify-center px-2"
      >
        <div
          class="absolute left-2 flex cursor-pointer items-center justify-center rounded-md py-2 text-white/80 hover:text-white"
        >
          <Bars3BottomLeftIcon
            class="h-10 w-10"
            @click="mobileNavOpen = true"
          />
        </div>

        <Link href="/">
          <Sealiac class="h-14 w-full" />
        </Link>
      </div>
    </div>

    <MobileNav
      :open="mobileNavOpen"
      @close="mobileNavOpen = false"
    />
  </div>
</template>
