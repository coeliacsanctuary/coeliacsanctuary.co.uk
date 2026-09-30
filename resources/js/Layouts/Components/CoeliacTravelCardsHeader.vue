<script setup lang="ts">
import { Bars3BottomLeftIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';
import { ref } from 'vue';
import MobileNav from '@/Layouts/Components/Nav/MobileNav.vue';
import CoeliacMetas from '@/Layouts/Components/CoeliacMetas.vue';
import Sealiac from '@/Svg/Sealiac.vue';
import { Link, router } from '@inertiajs/vue3';
import { MetaProps } from '@/types/DefaultProps';

defineProps<{ metas: MetaProps; showBack: boolean }>();

const mobileNavOpen = ref(false);
</script>

<template>
  <CoeliacMetas :metas="metas" />

  <div class="main-header relative h-14 bg-primary shadow-lg">
    <div
      id="header"
      class="relative z-20"
    >
      <div
        class="relative mx-auto flex w-full px-2"
        :class="
          showBack ? 'justify-start text-white' : 'items-center justify-center'
        "
      >
        <div
          v-if="!showBack"
          class="absolute left-2 flex cursor-pointer items-center justify-center rounded-md py-2 text-white/80 hover:text-white"
        >
          <Bars3BottomLeftIcon
            class="h-10 w-10"
            @click="mobileNavOpen = true"
          />
        </div>

        <Link
          v-if="showBack"
          class="flex h-14 items-center space-x-4"
          href="/"
        >
          <ArrowLeftIcon class="h-10 w-10" />
          <span class="text-3xl leading-none">Back</span>
        </Link>

        <Link
          v-if="!showBack"
          href="/"
        >
          <Sealiac class="h-14 w-full" />
        </Link>
      </div>
    </div>

    <MobileNav
      v-if="!showBack"
      :open="mobileNavOpen"
      url-prefix="https://www.coeliacsanctuary.co.uk"
      @close="mobileNavOpen = false"
    />
  </div>
</template>
