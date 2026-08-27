<script setup lang="ts">
import Card from '@/Components/Card.vue';
import SubHeading from '@/Components/SubHeading.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { Link } from '@inertiajs/vue3';
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

defineProps<{
  searchTerm: string;
}>();
</script>

<template>
  <Card class="space-y-4">
    <SubHeading
      as="h2"
      text-size="small"
      classes="flex items-center gap-3"
    >
      <MagnifyingGlassIcon class="size-7 shrink-0 text-primary" />

      <span>I couldn't find {{ searchTerm }}</span>
    </SubHeading>

    <div
      class="mx-auto h-px w-full bg-linear-to-r from-secondary/40 via-secondary/60 to-secondary/40"
    />

    <div class="prose max-w-none md:prose-lg">
      <p>
        My cards are listed by country and by language, so try searching for
        where the country rather than the town or city —
        <strong>France</strong> rather than <strong>Paris</strong>, or
        <strong>Spain</strong> rather than <strong>Benidorm</strong>.
      </p>

      <p>
        If you're visiting more than one country, search them one at a time and
        I'll show you what I've got for each — or take a look at my Full Set,
        which covers every language I make.
      </p>
    </div>

    <div class="grid gap-3 sm:grid-cols-3">
      <CoeliacButton
        :as="Link"
        href="/shop/product/full-set-coeliac-gluten-free-travel-translation-card"
        label="View the Full Set"
        classes="w-full justify-center text-center"
        theme="secondary"
        size="lg"
        bold
      />

      <CoeliacButton
        :as="Link"
        href="/shop/standard-coeliac-travel-cards"
        label="All Coeliac cards"
        classes="w-full justify-center text-center"
        theme="light"
        size="lg"
        bold
      />

      <CoeliacButton
        :as="Link"
        href="/shop/coeliac-plus-other-allergen-cards"
        label="All Coeliac+ cards"
        classes="w-full justify-center text-center"
        theme="light"
        size="lg"
        bold
      />
    </div>
  </Card>
</template>
