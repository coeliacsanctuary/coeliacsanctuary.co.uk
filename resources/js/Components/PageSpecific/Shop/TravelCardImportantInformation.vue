<script setup lang="ts">
import Card from '@/Components/Card.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
</script>

<template>
  <div class="relative -mt-1 px-3">
    <Card class="flex flex-col space-y-3">
      <p class="prose prose-lg max-w-none">
        These travel cards are designed as a communication aid to help explain
        gluten free dietary requirements and Coeliac disease in other languages.
      </p>

      <p class="prose prose-lg max-w-none">
        Translations are professionally prepared using native speakers; however,
        understanding and interpretation may vary between individuals and
        establishments.
      </p>

      <p class="prose prose-lg max-w-none">
        The cards do not guarantee that food provided will be gluten free or
        safe to eat. Users should continue to make their own assessment of food
        suitability and allergen safety.
      </p>

      <p class="prose prose-lg max-w-none">
        <a
          href="/terms-of-use#shop-travel-cards"
          target="_blank"
        >
          Read our full travel card policy
        </a>
      </p>
    </Card>
  </div>
</template>
