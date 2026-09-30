<script lang="ts" setup>
import Sidebar from '@/Components/Overlays/Sidebar.vue';
import { Link } from '@inertiajs/vue3';
import { watch } from 'vue';
import UseGoogleEvents from '@/composables/useGoogleEvents';

const emit = defineEmits(['close']);

const props = withDefaults(
  defineProps<{ open: boolean; urlPrefix?: string }>(),
  { urlPrefix: '' },
);

const close = () => emit('close');

const links: { label: string; href: string }[] = [
  { label: 'Home', href: `${props.urlPrefix}/` },
  { label: 'Shop', href: `${props.urlPrefix}/shop` },
  {
    label: 'Gluten Free Travel Cards',
    href: `${props.urlPrefix}/gluten-free-travel-translation-cards`,
  },
  { label: 'Blogs', href: `${props.urlPrefix}/blog` },
  { label: 'Eating Out', href: `${props.urlPrefix}/eating-out` },
  { label: 'Recipes', href: `${props.urlPrefix}/recipe` },
  { label: 'Collections', href: `${props.urlPrefix}/collection` },
  { label: 'About Us', href: `${props.urlPrefix}/about` },
  { label: 'Work With Us', href: `${props.urlPrefix}/work-with-us` },
  { label: 'Contact', href: `${props.urlPrefix}/contact` },
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
          class="flex flex-col space-y-3 text-center font-coeliac text-[1.65rem] font-semibold text-white"
        >
          <li
            v-for="(link, index) in links"
            :key="index"
          >
            <component
              :is="urlPrefix ? 'a' : Link"
              :href="link.href"
              class="block w-full px-2"
              prefetch
              @click="close()"
            >
              {{ link.label }}
            </component>
          </li>
        </ul>
      </nav>
    </div>
  </Sidebar>
</template>
