<script setup lang="ts">
import Card from '@/Components/Card.vue';
import { Link } from '@inertiajs/vue3';
import { EaterySimpleHomeResource } from '@/types/EateryTypes';

defineProps<{ eateries: EaterySimpleHomeResource[] }>();
</script>

<template>
  <Card class="-m-4 space-y-4 lg:m-0 pb-10">
    <h3 class="text-center text-2xl font-semibold text-primary-dark">
      Latest Eateries
    </h3>

    <ul class="divide-y divide-primary-dark/80">
      <li
        v-for="eatery in eateries"
        :key="eatery.link"
        class="flex flex-col p-2"
      >
        <Link
          :href="eatery.link"
          class="text-lg font-semibold text-primary-dark hover:text-black hover:underline"
          prefetch
        >
          {{ eatery.name }}
        </Link>

        <Link
          :href="eatery.location.link"
          class="hover:underline"
        >
          {{ eatery.location.name }}
        </Link>
        <span
          class="mt-4! block text-sm italic"
          v-text="eatery.created_at"
        />
      </li>
    </ul>
  </Card>
</template>
