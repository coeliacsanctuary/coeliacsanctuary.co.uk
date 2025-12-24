<script lang="ts" setup>
import CoeliacCompactHeader from '@/Layouts/Components/CoeliacCompactHeader.vue';
import CoeliacCompactFooter from '@/Layouts/Components/CoeliacCompactFooter.vue';
import { MetaProps } from '@/types/DefaultProps';
import { onMounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

defineProps<{ meta: MetaProps }>();

// onMounted(() => {
//   router.on('success', () => {
//     document
//       .querySelector('body')
//       ?.classList.toggle(
//         'no-auto-ads',
//         usePage().url.includes('/shop') ||
//           usePage().url.includes('/wheretoeat/browse'),
//       );
//   });
// });
</script>

<template>
  <div class="flex h-screen flex-col bg-gray-100">
    <CoeliacCompactHeader :metas="meta" />

    <section class="mx-auto mb-3 flex w-full flex-1 flex-col space-y-3">
      <slot />
    </section>

    <CoeliacCompactFooter />
  </div>
</template>
