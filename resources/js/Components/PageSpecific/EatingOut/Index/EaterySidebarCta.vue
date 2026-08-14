<script setup lang="ts">
import Card from '@/Components/Card.vue';
import SubHeading from '@/Components/SubHeading.vue';
import Icon from '@/Components/Icon.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { Link } from '@inertiajs/vue3';
import { ArrowRightIcon } from '@heroicons/vue/24/outline';
import useJourneyTracking from '@/composables/useJourneyTracking';

const props = defineProps<{
  icon: string;
  title: string;
  href: string;
  label: string;
  identifier: string;
}>();

const logClick = (): void => {
  useJourneyTracking().logEvent('clicked', props.identifier);
};
</script>

<template>
  <Card
    theme="primary"
    faded
    class="flex flex-col space-y-3"
  >
    <div class="flex items-center space-x-3">
      <Icon
        :name="icon"
        class="size-8 shrink-0 text-secondary"
      />

      <SubHeading
        text-size="small"
        as="h3"
      >
        {{ title }}
      </SubHeading>
    </div>

    <p class="prose max-w-none">
      <slot />
    </p>

    <CoeliacButton
      :as="Link"
      :href="href"
      :label="label"
      theme="primary"
      size="lg"
      bold
      icon-position="right"
      :icon="ArrowRightIcon"
      classes="justify-center"
      @click="logClick"
    />
  </Card>
</template>
