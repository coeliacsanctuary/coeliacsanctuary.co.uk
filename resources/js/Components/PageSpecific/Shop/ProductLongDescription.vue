<script setup lang="ts">
import { onMounted, ref, useTemplateRef } from 'vue';
import Card from '@/Components/Card.vue';
import SubHeading from '@/Components/SubHeading.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { ChevronDownIcon, ChevronUpIcon } from '@heroicons/vue/24/solid';

defineProps<{ description: string }>();

const body = useTemplateRef<HTMLElement>('body');

const isExpanded = ref(false);

// Rendered unclamped on the server so the full text is always in the markup for crawlers, then
// clamped on mount only when there is meaningfully more to read than the collapsed height shows.
const isClamped = ref(false);

onMounted(() => {
  if (!body.value) {
    return;
  }

  isClamped.value = body.value.scrollHeight > 500;
});
</script>

<template>
  <Card class="space-y-4">
    <SubHeading classes="text-primary-dark">Full Description</SubHeading>

    <div class="relative">
      <div
        ref="body"
        class="prose prose-lg max-w-none transition-[max-height] duration-300 md:prose-xl"
        :class="isClamped && !isExpanded ? 'max-h-[26rem] overflow-hidden' : ''"
        v-html="description"
      />

      <div
        v-if="isClamped && !isExpanded"
        class="pointer-events-none absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-white to-transparent"
      />
    </div>

    <CoeliacButton
      v-if="isClamped"
      as="button"
      type="button"
      theme="light"
      size="xl"
      :label="isExpanded ? 'Show less' : 'Read the full description'"
      :icon="isExpanded ? ChevronUpIcon : ChevronDownIcon"
      icon-position="right"
      classes="w-full justify-center text-center"
      bold
      @click="isExpanded = !isExpanded"
    />
  </Card>
</template>
