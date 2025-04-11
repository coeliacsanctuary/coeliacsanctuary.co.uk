<script setup lang="ts">
import {
  Bars3BottomLeftIcon,
  MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline';
import { ref } from 'vue';
import MobileNav from '@/Layouts/Components/Nav/MobileNav.vue';
import CoeliacMetas from '@/Layouts/Components/CoeliacMetas.vue';
import MainNav from '@/Layouts/Components/Nav/MainNav.vue';
import Sealiac from '@/Svg/Sealiac.vue';
import { Link } from '@inertiajs/vue3';
import { MetaProps } from '@/types/DefaultProps';
import MobileSearch from '@/Layouts/Components/MobileSearch.vue';

defineProps<{ metas: MetaProps }>();

const mobileNavOpen = ref(false);
const mobileSearchOpen = ref(false);
</script>

<template>
  <CoeliacMetas :metas="metas" />

  <div class="relative bg-primary shadow-lg">
    <div
      id="header"
      class="relative z-20"
    >
      <div
        class="mx-auto flex w-full items-start justify-between px-2 lg:relative"
      >
        <div
          class="flex items-center justify-center rounded-md py-2 text-white/80 hover:text-white lg:hidden"
        >
          <Bars3BottomLeftIcon
            class="h-10 w-10"
            @click="mobileNavOpen = true"
          />
        </div>

        <Link
          href="/"
          class="lg:absolute lg:left-2"
        >
          <Sealiac class="h-14 w-full" />
        </Link>

        <MainNav class="md:hidden! lg:flex!" />

        <div class="lg:absolute lg:right-2">
          <div class="h-10 w-10 py-2">
            <div
              class="flex h-10 w-10 items-center justify-center rounded-full bg-secondary"
              @click="mobileSearchOpen = true"
            >
              <MagnifyingGlassIcon class="h-6 w-6" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <MobileNav
      :open="mobileNavOpen"
      @close="mobileNavOpen = false"
    />

    <MobileSearch
      :open="mobileSearchOpen"
      @close="mobileSearchOpen = false"
    />
  </div>
</template>
