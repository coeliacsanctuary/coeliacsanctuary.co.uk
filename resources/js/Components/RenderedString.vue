<script setup lang="ts">
import {
  shallowRef,
  onMounted,
  defineProps,
  compile,
  defineComponent,
} from 'vue';
import ArticleHeader from './ArticleHeader.vue';
import ArticleImage from './ArticleImage.vue';
import { CustomComponent } from '@/types/Types';
const props = defineProps<{ content: string }>();

const compiled = shallowRef<CustomComponent | null>(null);

onMounted(() => {
  try {
    const renderFn = compile(props.content);

    compiled.value = defineComponent({
      render: renderFn,
    });
  } catch (error) {
    //
  }
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
