<script lang="ts" setup>
import Sidebar from '@/Components/Overlays/Sidebar.vue';
import { Link } from '@inertiajs/vue3';
import { watch } from 'vue';
import UseGoogleEvents from '@/composables/useGoogleEvents';

const emit = defineEmits(['close']);

const props = defineProps<{ open: boolean }>();

const close = () => emit('close');

const links: { label: string; href: string }[] = [
  { label: 'Home', href: '/' },
  { label: 'Shop', href: '/shop' },
  {
    label: 'Gluten Free Travel Cards',
    href: '/gluten-free-travel-translation-cards',
  },
  { label: 'Blogs', href: '/blog' },
  { label: 'Eating Out', href: '/eating-out' },
  { label: 'Recipes', href: '/recipe' },
  { label: 'Collections', href: '/collection' },
  { label: 'About Us', href: '/about' },
  { label: 'Work With Us', href: '/work-with-us' },
  { label: 'Contact', href: '/contact' },
];

watch(
  () => props.open,
  () => {
    if (!props.open) {
      return;
    }

    UseGoogleEvents().googleEvent('event', 'mobile-nav', {
      event_category: 'opened-mobile-nav',
    });
  },
);
</script>

<template>
  <Sidebar
    :open="open"
    side="right"
    @close="close()"
  >
    <div class="flex-1 bg-primary">
      <nav class="w-full pt-10">
        <ul
          class="flex flex-col space-y-3 text-center text-[1.65rem] font-coeliac text-white font-semibold"
        >
          <li
            v-for="(link, index) in links"
            :key="index"
          >
            <Link
              :href="link.href"
              class="block w-full px-2"
              prefetch
              @click="close()"
            >
              {{ link.label }}
            </Link>
          </li>
        </ul>
      </nav>
    </div>
  </Sidebar>
</template>
