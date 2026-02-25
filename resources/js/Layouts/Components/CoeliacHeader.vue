<script setup lang="ts">
import {
  Bars3BottomLeftIcon,
  MagnifyingGlassIcon,
  ChatBubbleOvalLeftEllipsisIcon,
} from '@heroicons/vue/24/outline';
import { onMounted, ref } from 'vue';
import MobileNav from '@/Layouts/Components/Nav/MobileNav.vue';
import MainNav from '@/Layouts/Components/Nav/MainNav.vue';
import Sealiac from '@/Svg/Sealiac.vue';
import { Link } from '@inertiajs/vue3';
import { MetaProps } from '@/types/DefaultProps';
import MobileSearch from '@/Layouts/Components/MobileSearch.vue';
import DesktopSearch from '@/Layouts/Components/DesktopSearch.vue';
import CoeliacMetas from '@/Layouts/Components/CoeliacMetas.vue';
import AskSealiac from '@/Layouts/Components/AskSealiac/AskSealiac.vue';

defineProps<{ metas: MetaProps }>();

const mobileNavOpen = ref(false);
const mobileSearchOpen = ref(false);
const askSealiacOpen = ref(false);
const isMounted = ref(false);

onMounted(() => {
  isMounted.value = true;
});
</script>

<template>
  <CoeliacMetas :metas="metas" />

  <header class="main-header relative bg-primary shadow-lg">
    <div
      id="header"
      class="relative z-20"
    >
      <div
        class="mx-auto flex w-full max-w-8xl items-start justify-between gap-2 px-2"
      >
        <div
          class="flex items-center justify-center rounded-md py-2 text-white/80 hover:text-white md:hidden"
        >
          <Bars3BottomLeftIcon
            class="h-10 w-10"
            @click="mobileNavOpen = true"
          />
        </div>

        <Link href="/">
          <Sealiac class="w-full py-2" />
        </Link>

        <div
          class="flex space-x-1 xs:flex-col xs:space-y-1 xs:space-x-0 lg:flex-1 lg:flex-row lg:items-center lg:justify-end lg:space-y-0"
        >
          <div class="md:w-full md:max-w-xs">
            <div class="size-8 py-2 xs:size-10 md:hidden">
              <div
                class="flex size-8 items-center justify-center rounded-full bg-secondary xs:size-10"
                @click="mobileSearchOpen = true"
              >
                <MagnifyingGlassIcon class="size-5 sm:size-7" />
              </div>
            </div>

            <DesktopSearch />
          </div>

          <div
            class="size-8 py-2 xs:size-10 md:flex md:h-auto md:w-auto md:justify-end"
          >
            <div
              class="curosr-pointer flex size-8 cursor-pointer items-center justify-center rounded-full border-2 border-secondary bg-primary-light transition xs:size-10 md:h-auto md:!w-fit md:space-x-2 md:px-2 md:py-1 md:hover:bg-primary-darkest/50"
              @click="askSealiacOpen = true"
            >
              <ChatBubbleOvalLeftEllipsisIcon
                class="size-5 sm:size-7 md:flex-shrink-0"
              />
              <span class="break-none hidden font-semibold md:block">
                Ask Sealiac
              </span>
            </div>
          </div>
        </div>
      </div>

      <div
        class="mx-auto hidden h-px w-4/5 bg-linear-to-r from-white/20 via-white/40 to-white/20 md:block"
      />

      <MainNav @open-search="mobileSearchOpen = true" />
    </div>
  </header>

  <MobileNav
    :open="mobileNavOpen"
    @close="mobileNavOpen = false"
  />

  <MobileSearch
    :open="mobileSearchOpen"
    @close="mobileSearchOpen = false"
  />

  <AskSealiac
    v-if="isMounted"
    :open="askSealiacOpen"
    @close="askSealiacOpen = false"
  />
</template>

<style>
.main-header .adsbygoogle,
.main-header .google-auto-placed {
  display: none;
}
</style>
