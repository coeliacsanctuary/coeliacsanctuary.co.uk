<script setup lang="ts">
import CoeliacButton from '@/Components/CoeliacButton.vue';
import FormTextarea from '@/Components/Forms/FormTextarea.vue';
import { computed, nextTick, onMounted, ref } from 'vue';

const placeholders = [
  'What could I make with some chocolate, eggs and flour?',
  'Where can I eat gluten free in Manchester?',
  'Do you have a gluten free banana bread recipe?',
  "I'm travelling to Spain, which travel card do I need?",
  "I've got chicken, rice and peppers - any recipe ideas?",
  'Can you suggest a dairy free dinner recipe?',
  'What travel cards do you have available?',
  'Where can I find gluten free food in Edinburgh?',
  'Do you have any vegan breakfast recipes?',
  'What gluten free places are there in London?',
];

const placeholder = ref('');

const randomisePlaceholder = () => {
  const remaining = placeholders.filter((p) => p !== placeholder.value);

  placeholder.value = remaining[Math.floor(Math.random() * remaining.length)];
};

onMounted(randomisePlaceholder);

const props = defineProps<{
  isFetching: boolean;
  isStreaming: boolean;
  limitReached: boolean;
}>();

const prompt = ref('');

const emit = defineEmits(['send-prompt']);

const disableButton = computed(() => {
  if (props.limitReached) {
    return true;
  }

  if (props.isFetching) {
    return true;
  }

  if (props.isStreaming) {
    return true;
  }

  if (prompt.value.length < 3) {
    return true;
  }

  if (prompt.value.length > 500) {
    return true;
  }

  return false;
});

const submitPrompt = () => {
  emit('send-prompt', prompt.value);

  nextTick(() => {
    prompt.value = '';
    randomisePlaceholder();
  });
};
</script>

<template>
  <div class="shrink-0 border-t border-grey-off bg-grey-light p-2 sm:p-4">
    <div
      class="flex rounded-md border border-grey-off bg-white focus:border-grey-dark"
    >
      <FormTextarea
        v-model="prompt"
        label=""
        hide-label
        name="prompt"
        :resizable="false"
        :shadow="false"
        class="flex-1 text-sm sm:!text-base"
        :placeholder="placeholder"
        :max="500"
        @keydown.enter.exact.prevent="!disableButton && submitPrompt()"
        @keydown.meta.enter.prevent="prompt += '\n'"
        @keydown.ctrl.enter.prevent="prompt += '\n'"
      />

      <div class="flex flex-col justify-between p-2">
        <CoeliacButton
          label="Send"
          size="sm"
          as="button"
          :disabled="disableButton"
          @click="() => submitPrompt()"
        />

        <span class="text-right text-xs font-semibold text-grey-darker">
          {{ prompt.length }} / 500
        </span>
      </div>
    </div>
  </div>
</template>
