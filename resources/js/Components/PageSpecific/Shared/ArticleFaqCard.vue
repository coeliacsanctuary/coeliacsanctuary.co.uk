<script lang="ts" setup>
import { ref } from 'vue';
import Card from '@/Components/Card.vue';
import SubHeading from '@/Components/SubHeading.vue';
import ArticleFaqItem from '@/Components/PageSpecific/Shared/ArticleFaqItem.vue';
import { ArticleFaq } from '@/types/Types';

defineProps<{
  faqs: ArticleFaq[];
  title: string;
}>();

const openIndex = ref<number | null>(null);

const handleOpen = (index: number): void => {
  openIndex.value = openIndex.value === index ? null : index;
};
</script>

<template>
  <Card>
    <SubHeading classes="text-primary-dark">
      {{ title }}
    </SubHeading>

    <div class="mt-3 flex flex-col space-y-4">
      <ArticleFaqItem
        v-for="(faq, index) in faqs"
        :key="faq.question"
        :faq="faq"
        :index="index"
        :is-open="openIndex === index"
        @open="handleOpen"
      />
    </div>
  </Card>
</template>
