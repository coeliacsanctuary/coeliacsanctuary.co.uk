<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { computed } from 'vue';

const props = defineProps<{ author: string }>();

const isJamie = computed(() => props.author === 'Jamie Peters');
</script>

<template>
  <Card
    faded
    theme="primary-light"
  >
    <div class="justify-center md:flex md:flex-row md:space-x-2 md:space-x-4">
      <img
        :alt="isJamie ? 'Jamie Peters' : 'Alison Peters'"
        class="float-left mr-2 mb-2 w-1/4 max-w-[150px] rounded-full"
        :src="isJamie ? '/images/misc/jamie.png' : '/images/misc/alison.png'"
      />

      <div class="prose max-w-2xl md:prose-xl">
        <template v-if="isJamie">
          While not a coeliac, <strong>Jamie Peters</strong> is married to one,
          he's the brains behind this website, and alongside software
          development he has always enjoyed cooking and baking, and will adapt
          his old family favourites so Alison can eat them too.
        </template>

        <template v-else>
          <strong>Alison Peters</strong> has been Coeliac since June 2014 and
          launched Coeliac Sanctuary in August of that year, and since then has
          aimed to provide a one stop shop for Coeliacs, from blogs, to recipes,
          eating out guide and online shop.
        </template>
      </div>
    </div>
  </Card>
</template>
