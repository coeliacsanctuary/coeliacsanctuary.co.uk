<script setup lang="ts">
import {
  shallowRef,
  onMounted,
  defineProps,
  compile,
  defineComponent,
} from 'vue';
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
    <component :is="compiled" />
  </div>
  <div v-else>
    <div v-html="content"></div>
  </div>
</template>
