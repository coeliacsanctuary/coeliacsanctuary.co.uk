<script setup lang="ts">
import { InformationCircleIcon } from '@heroicons/vue/20/solid';

withDefaults(defineProps<{ border?: boolean; textClasses?: string }>(), {
  border: true,
  textClasses: 'text-xs',
});
</script>

<template>
  <div
    class="space-y-2 text-grey-darker"
    :class="{ 'border-t border-primary-light/60 pt-4': border }"
  >
    <p
      class="flex items-center gap-1.5 font-semibold text-grey-darkest"
      :class="textClasses"
    >
      <InformationCircleIcon class="size-4 shrink-0 text-primary-dark" />
      Before you buy
    </p>

    <p :class="textClasses">
      These travel cards are designed as a communication aid to help explain
      gluten free dietary requirements and Coeliac disease in other languages.
    </p>

    <p :class="textClasses">
      Translations are professionally prepared using native speakers; however,
      understanding and interpretation may vary between individuals and
      establishments.
    </p>

    <p :class="textClasses">
      The cards do not guarantee that food provided will be gluten free or safe
      to eat. Users should continue to make their own assessment of food
      suitability and allergen safety.
    </p>

    <p :class="textClasses">
      <a
        href="/terms-of-use#shop-travel-cards"
        target="_blank"
        class="font-semibold text-primary-dark underline"
      >
        Read my full travel card policy
      </a>
    </p>
  </div>
</template>
