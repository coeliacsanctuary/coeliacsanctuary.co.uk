<script setup lang="ts">
import Card from '@/Components/Card.vue';
import { Link } from '@inertiajs/vue3';
import { EaterySimpleHomeResource } from '@/types/EateryTypes';
import { ArrowRightIcon } from '@heroicons/vue/24/outline';

defineProps<{ eateries: EaterySimpleHomeResource[] }>();
</script>

<template>
  <Card class="space-y-4 pb-10">
    <h3 class="text-center text-2xl font-semibold text-primary-dark">
      Latest Eateries
    </h3>

    <p class="prose">
      Here are the latest places that have been added to our comprehensive
      <Link href="/eating-out">Eating Out</Link> guide.
    </p>

    <ul class="divide-y divide-primary-dark/80">
      <li
        v-for="eatery in eateries"
        :key="eatery.link"
        class="flex flex-col py-2"
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

    <p class="prose prose-lg">
      <Link
        href="/eating-out"
        class="inline-flex items-center space-x-2"
      >
        <span>View more places to eat!</span>
        <ArrowRightIcon class="size-4" />
      </Link>
    </p>
  </Card>
</template>
