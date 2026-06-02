<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import SubHeading from '@/Components/SubHeading.vue';
import { ArticleFaq } from '@/types/Types';

defineProps<{
  faqs: ArticleFaq[];
  title: string;
}>();
</script>

<template>
  <Card>
    <SubHeading classes="text-primary-dark">
      {{ title }}
    </SubHeading>

    <div
      v-for="faq in faqs"
      :key="faq.question"
      class="mt-3 divide-y divide-primary"
    >
      <div class="flex flex-col space-y-3">
        <h3
          class="text-xl font-semibold md:text-2xl"
          v-text="faq.question"
        />

        <p
          class="prose prose-lg max-w-none md:prose-xl"
          v-html="faq.answer"
        />
      </div>
    </div>
  </Card>
</template>
