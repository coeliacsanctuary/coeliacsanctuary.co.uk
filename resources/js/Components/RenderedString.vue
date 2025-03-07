<script setup lang="ts">
import { shallowRef, onMounted, defineProps } from 'vue';
import ArticleHeader from './ArticleHeader.vue';
import ArticleImage from './ArticleImage.vue';

const props = defineProps<{ content: string }>();
const compiled = shallowRef<(() => any) | null>(null);

onMounted(async () => {
  const { compile } = import.meta.env.PROD;
  // ? await import('vue/dist/vue.esm-bundler.js')
  await import('vue');
  compiled.value = compile(props.content);
});
</script>

<template>
  <div v-if="compiled">
    <component
      :is="compiled"
      :components="{ ArticleHeader, ArticleImage }"
    />
  </div>
  <div v-else>
    <div v-html="content"></div>
  </div>
</template>
