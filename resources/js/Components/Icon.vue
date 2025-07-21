<script lang="ts" setup>
import { computed, onMounted, ref } from 'vue';

const props = withDefaults(
  defineProps<{
    name: string;
    class?: string;
  }>(),
  { class: 'w-6 h-6' },
);

const filepath = `../../icons/${props.name}.svg`;

const modules = import.meta.glob('../../icons/*.svg', {
  as: 'raw',
  eager: true,
});

const isMounted = ref(false);

onMounted(() => {
  isMounted.value = true;
});

const svg = computed(() => {
  if (!isMounted.value) {
    return '';
  }

  const template = document.createElement('template');
  template.innerHTML = modules[filepath];

  const element: SVGElement = template.content.firstChild;

  element.setAttribute('class', props.class);

  return template.innerHTML;
});
</script>

<template>
  <div v-html="svg" />
</template>
